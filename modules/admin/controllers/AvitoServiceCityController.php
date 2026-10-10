<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\models\AvitoLocation;
use app\models\AvitoServiceCity;
use app\modules\admin\models\search\AvitoServiceCitySearch;
use yii\db\ActiveRecord;

class AvitoServiceCityController extends CrudController
{
    protected function modelClass(): string
    {
        return AvitoServiceCity::class;
    }

    protected function searchModelClass(): string
    {
        return AvitoServiceCitySearch::class;
    }

    protected function entityTitle(): string
    {
        return 'Города публикации';
    }

    protected function recordLabel(ActiveRecord $model): string
    {
        if (!$model instanceof AvitoServiceCity) {
            return parent::recordLabel($model);
        }

        return self::locationName($model->location, $model->location_id);
    }

    protected function newModel(): ActiveRecord
    {
        $model = new AvitoServiceCity();
        $model->is_enabled = true;
        $model->priority = 100;

        return $model;
    }

    protected function beforePersist(ActiveRecord $model): void
    {
        if (!$model instanceof AvitoServiceCity) {
            return;
        }

        if ($model->is_enabled === null) {
            $model->is_enabled = true;
        }
        if ($model->priority === null) {
            $model->priority = 100;
        }
    }

    protected function gridColumns(): array
    {
        return [
            'id',
            [
                'attribute' => 'location_id',
                'value' => static fn (AvitoServiceCity $model, ...$extra): string => self::locationName($model->location, $model->location_id),
            ],
            [
                'attribute' => 'is_enabled',
                'format' => 'boolean',
                'filter' => ['1' => 'Да', '0' => 'Нет'],
            ],
            'priority',
            $this->timeColumn('updated_at'),
        ];
    }

    protected function detailAttributes(ActiveRecord $model): array
    {
        return [
            'id',
            [
                'attribute' => 'location_id',
                'value' => static fn (AvitoServiceCity $model, ...$extra): string => self::locationName($model->location, $model->location_id),
            ],
            'is_enabled:boolean',
            'priority',
            $this->timeColumn('created_at'),
            $this->timeColumn('updated_at'),
        ];
    }

    protected function formFields(ActiveRecord $model): array
    {
        $hint = 'ID города из справочника локаций Avito.';
        if ($model instanceof AvitoServiceCity && $model->location !== null) {
            $hint = 'Сейчас: ' . self::locationName($model->location, $model->location_id);
        }

        return [
            'location_id' => ['widget' => 'number', 'hint' => $hint],
            'is_enabled' => ['widget' => 'checkbox'],
            'priority' => [
                'widget' => 'number',
                'hint' => 'Меньше — раньше в обходе.',
            ],
        ];
    }

    private static function locationName(?AvitoLocation $location, mixed $id): string
    {
        if ($location === null) {
            return $id === null || $id === '' ? '—' : (string) $id;
        }

        $name = $location->full_name !== '' ? $location->full_name : $location->name;

        return $name . ' (#' . $location->id . ')';
    }
}
