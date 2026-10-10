<?php

declare(strict_types=1);

namespace app\services\avito;

use app\components\avito\AvitoAuth;
use app\components\avito\AvitoClient;
use Yii;
use yii\base\Component;

/**
 * Сервис работы с объявлениями Avito (задел).
 *
 * Часть методов дублирует AvitoHierarchyService (linkItems/listItemsByEmployeeId)
 * и предназначена для последующего выноса логики объявлений.
 */
class AvitoItemsService extends Component
{
    public ?AvitoClient $client = null;
    public ?AvitoAuth $auth = null;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        if ($this->client === null) {
            $this->client = Yii::$app->get('avitoClient');
        }

        if ($this->auth === null) {
            $this->auth = Yii::$app->get('avitoAuth');
        }
    }

    /**
     * Прикрепление сотрудника иерархии к объявлениям.
     *
     * @param int[] $itemIds
     */
    public function linkItems(string $accountId, int $employeeId, array $itemIds): void
    {
        $this->client->post('/linkItemsV1', [
            'employeeId' => $employeeId,
            'itemIds' => array_values($itemIds),
        ], $this->auth->getAccessToken($accountId));
    }

    /**
     * Список объявлений по сотруднику.
     *
     * @return array<string, mixed>
     */
    public function listByEmployeeId(
        string $accountId,
        int $employeeId,
        int $categoryId,
        ?int $lastItemId = null,
    ): array {
        return $this->client->request('POST', '/listItemsByEmployeeIdV1', [
            'body' => array_filter([
                'employeeId' => $employeeId,
                'categoryId' => $categoryId,
                'lastItemId' => $lastItemId,
            ], static fn ($value): bool => $value !== null),
            'accessToken' => $this->auth->getAccessToken($accountId),
        ]);
    }
}

