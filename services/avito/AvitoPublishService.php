<?php

declare(strict_types=1);

namespace app\services\avito;

use app\exceptions\avito\AvitoException;
use app\models\AvitoAccount;
use app\models\AvitoService;
use Yii;
use yii\base\Component;

/**
 * Публикация объявлений через XML-фид Автозагрузки и обработка отчёта.
 */
class AvitoPublishService extends Component
{
    /** Размер батча объявлений на один фид */
    public int $batchSize = 100;

    /**
     * Формирует фид из очередного батча черновиков.
     *
     * @return array{feed_path: string, batch_size: int}
     */
    public function buildFeed(AvitoAccount $account): array
    {
        $feedBuilder = Yii::$app->get('avitoFeedBuilder');

        $batch = AvitoService::findDraftBatch((int) $account->id, $this->batchSize);

        if ($batch === []) {
            return ['feed_path' => $account->feed_path, 'batch_size' => 0];
        }

        // Помечаем pending до записи фида, чтобы повторный запуск не взял их снова
        $ids = array_map(static fn (AvitoService $service): int => (int) $service->id, $batch);
        AvitoService::updateAll(
            ['status' => AvitoService::STATUS_PENDING, 'updated_at' => time()],
            ['id' => $ids],
        );

        $xml = $feedBuilder->build($batch);

        $path = (string) $account->feed_path;
        $dir = dirname($path);

        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        if (@file_put_contents($path, $xml) === false) {
            // откатываем статусы, если не смогли записать файл
            AvitoService::updateAll(
                ['status' => AvitoService::STATUS_DRAFT, 'updated_at' => time()],
                ['id' => $ids],
            );

            throw new AvitoException('Не удалось записать фид: ' . $path);
        }

        return ['feed_path' => $path, 'batch_size' => count($batch)];
    }

    /**
     * Применяет отчёт Автозагрузки к объявлениям.
     *
     * @param array<int, array<string, mixed>> $items строки отчёта: id, status, avito_id, error{code,message}
     * @return array{success: int, failed: int}
     */
    public function applyReport(int $accountId, array $items): array
    {
        $success = 0;
        $failed = 0;
        $now = time();

        foreach ($items as $row) {
            $itemId = (string) ($row['id'] ?? '');
            if ($itemId === '') {
                continue;
            }

            $service = AvitoService::findOne([
                'account_id' => $accountId,
                'autoload_item_id' => $itemId,
            ]);

            if ($service === null) {
                continue;
            }

            if (($row['status'] ?? '') === 'success') {
                $service->status = AvitoService::STATUS_PUBLISHED;
                $service->avito_item_id = isset($row['avito_id']) ? (int) $row['avito_id'] : null;
                $service->published_at = $now;
                $service->last_error_code = null;
                $service->last_error_message = null;
                $success++;
            } else {
                $service->status = AvitoService::STATUS_FAILED;
                $service->last_error_code = isset($row['error']['code'])
                    ? mb_substr((string) $row['error']['code'], 0, 32)
                    : null;
                $service->last_error_message = isset($row['error']['message'])
                    ? (string) $row['error']['message']
                    : null;
                $failed++;
            }

            $service->save(false);
        }

        return ['success' => $success, 'failed' => $failed];
    }

    /**
     * Возвращает ошибочные объявления в draft для повторной публикации.
     */
    public function resetFailedToDraft(int $accountId): int
    {
        return AvitoService::updateAll(
            [
                'status' => AvitoService::STATUS_DRAFT,
                'last_error_code' => null,
                'last_error_message' => null,
                'updated_at' => time(),
            ],
            ['account_id' => $accountId, 'status' => AvitoService::STATUS_FAILED],
        );
    }
}
