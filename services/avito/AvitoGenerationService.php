<?php

declare(strict_types=1);

namespace app\services\avito;

use app\exceptions\avito\AvitoException;
use app\models\AvitoLocation;
use app\models\AvitoService;
use app\models\AvitoServiceCity;
use Yii;
use yii\base\Component;

/**
 * Генерация объявлений услуг по выбранным городам.
 *
 * Шаблон текста задаётся методом renderTitle/renderDescription.
 */
class AvitoGenerationService extends Component
{
    /**
     * Шаблон заголовка. Плейсхолдеры: {city}, {region}, {n}
     */
    public string $titlePattern = 'Семейный психолог в {city} — кризис в отношениях';

    /**
     * Шаблон описания. Плейсхолдеры: {city}, {region}, {n}
     */
    public string $descriptionPattern = "Семейный психолог в {city} ({region}).\n"
        . 'Помогу разобраться в кризисе отношений, изменах и семейных конфликтах. '
        . "Консультации очно и онлайн, часовой пояс учитывается.\n\nОбъявление №{n}.";

    /** Базовая цена */
    public int $price = 1500;

    /**
     * Создаёт $perCity объявлений для каждого включённого города.
     *
     * @param AvitoServiceCity[]|null $cities Если null — берутся все включённые
     * @return int Количество созданных объявлений
     */
    public function generate(int $accountId, int $perCity, ?array $cities = null): int
    {
        $cities ??= AvitoServiceCity::find()->alias('c')
            ->joinWith('location l', false)
            ->where(['c.is_enabled' => true, 'l.is_active' => true])
            ->orderBy(['c.priority' => SORT_ASC, 'c.id' => SORT_ASC])
            ->all();

        $created = 0;

        foreach ($cities as $serviceCity) {
            $location = $serviceCity->location;

            if (!$location instanceof AvitoLocation) {
                continue;
            }

            for ($n = 1; $n <= $perCity; $n++) {
                if ($this->exists($accountId, $location, $n)) {
                    continue;
                }

                if ($this->createOne($accountId, $location, $n)) {
                    $created++;
                }
            }
        }

        return $created;
    }

    private function exists(int $accountId, AvitoLocation $location, int $n): bool
    {
        return AvitoService::find()->where([
            'account_id' => $accountId,
            'autoload_item_id' => $this->buildItemId($location, $n),
        ])->exists();
    }

    private function createOne(int $accountId, AvitoLocation $location, int $n): bool
    {
        $service = new AvitoService();
        $service->account_id = $accountId;
        $service->location_id = $location->id;
        $service->autoload_item_id = $this->buildItemId($location, $n);
        $service->city = $location->name;
        $service->region = $location->full_name;
        $service->title = $this->render($this->titlePattern, $location, $n);

        if (mb_strlen($service->title) > 255) {
            throw new AvitoException('Заголовок превышает 255 символов: ' . $service->title);
        }

        $service->description = $this->render($this->descriptionPattern, $location, $n);
        $service->price = $this->price;
        $service->status = AvitoService::STATUS_DRAFT;

        return $service->save(false);
    }

    /**
     * Стабильный id для фида: psy-{slug}-{n:03}.
     */
    public function buildItemId(AvitoLocation $location, int $n): string
    {
        return sprintf('psy-%s-%03d', $location->slug, $n);
    }

    private function render(string $template, AvitoLocation $location, int $n): string
    {
        return strtr($template, [
            '{city}' => $location->name,
            '{region}' => $location->full_name,
            '{n}' => (string) $n,
        ]);
    }
}
