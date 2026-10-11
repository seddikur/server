<?php

declare(strict_types=1);

namespace app\modules\setting\controllers;

use app\modules\admin\controllers\CrudController;
use app\modules\admin\helpers\Format;
use app\modules\setting\models\Setting;
use app\modules\setting\models\search\SettingSearch;
use yii\db\ActiveRecord;

class SettingsController extends CrudController
{
    protected function modelClass(): string
    {
        return Setting::class;
    }

    protected function searchModelClass(): string
    {
        return SettingSearch::class;
    }

    protected function entityTitle(): string
    {
        return 'Настройки';
    }

    protected function recordLabel(ActiveRecord $model): string
    {
        return $model instanceof Setting ? (string) $model->key : parent::recordLabel($model);
    }

    protected function gridColumns(): array
    {
        return [
            'id',
            'key',
            [
                'attribute' => 'value',
                'value' => static fn (Setting $model, ...$extra): string => Format::excerpt($model->value, 80),
            ],
            'description',
            $this->timeColumn('updated_at'),
        ];
    }

    protected function detailAttributes(ActiveRecord $model): array
    {
        return [
            'id',
            'key',
            'value:ntext',
            'description',
            $this->timeColumn('created_at'),
            $this->timeColumn('updated_at'),
        ];
    }

    protected function formFields(ActiveRecord $model): array
    {
        $value = ['widget' => 'textarea', 'rows' => 6, 'wide' => true];
        if ($model instanceof Setting && $model->isBoolean()) {
            $value = [
                'widget' => 'dropdown',
                'items' => ['0' => 'Выключено', '1' => 'Включено'],
                'prompt' => '—',
            ];
        }

        return [
            'key' => ['widget' => 'text'],
            'value' => $value,
            'description' => ['widget' => 'text', 'wide' => true],
        ];
    }
}
