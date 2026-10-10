<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\models\AvitoAccount;
use app\models\AvitoLocation;
use app\models\AvitoService;
use app\modules\admin\helpers\Format;
use app\modules\admin\helpers\Options;
use app\modules\admin\models\search\AvitoServiceSearch;
use yii\db\ActiveRecord;
use yii\helpers\Html;

class AvitoServiceController extends CrudController
{
    protected function modelClass(): string
    {
        return AvitoService::class;
    }

    protected function searchModelClass(): string
    {
        return AvitoServiceSearch::class;
    }

    protected function entityTitle(): string
    {
        return 'Услуги Avito';
    }

    protected function recordLabel(ActiveRecord $model): string
    {
        return $model instanceof AvitoService ? (string) $model->title : parent::recordLabel($model);
    }

    protected function newModel(): ActiveRecord
    {
        $model = new AvitoService();
        $model->status = AvitoService::STATUS_DRAFT;
        $account = AvitoAccount::findActive();
        if ($account !== null) {
            $model->account_id = $account->id;
        }

        return $model;
    }

    protected function beforePersist(ActiveRecord $model): void
    {
        if ($model instanceof AvitoService && ($model->status === null || $model->status === '')) {
            $model->status = AvitoService::STATUS_DRAFT;
        }
    }

    protected function gridColumns(): array
    {
        return [
            'id',
            $this->accountColumn(),
            'city',
            'title',
            'price',
            [
                'attribute' => 'status',
                'filter' => AvitoService::statusOptions(),
                'value' => static fn (AvitoService $model, ...$extra): string => AvitoService::statusOptions()[$model->status] ?? (string) $model->status,
            ],
            'avito_item_id',
        ];
    }

    protected function detailAttributes(ActiveRecord $model): array
    {
        return [
            'id',
            $this->accountColumn(),
            [
                'attribute' => 'location_id',
                'value' => static fn (AvitoService $model, ...$extra): string => self::locationName($model->location, $model->location_id),
            ],
            'autoload_item_id',
            'city',
            'region',
            'title',
            'description:ntext',
            'price',
            [
                'attribute' => 'price_list',
                'format' => 'raw',
                'value' => static function (AvitoService $model, ...$extra): string {
                    $text = Format::jsonText($model->price_list);
                    if ($text === '') {
                        return '—';
                    }

                    return Html::tag('pre', Html::encode($text), ['class' => 'mb-0 small']);
                },
            ],
            'workplace',
            'provider_gender',
            'client_gender',
            'consultation_type',
            'approach',
            'experience',
            [
                'attribute' => 'status',
                'value' => static fn (AvitoService $model, ...$extra): string => AvitoService::statusOptions()[$model->status] ?? (string) $model->status,
            ],
            'avito_item_id',
            $this->timeColumn('published_at'),
            'last_error_code',
            'last_error_message:ntext',
            $this->timeColumn('created_at'),
            $this->timeColumn('updated_at'),
        ];
    }

    protected function formFields(ActiveRecord $model): array
    {
        return [
            'account_id' => [
                'widget' => 'dropdown',
                'items' => Options::accounts(),
                'prompt' => 'Выберите аккаунт',
            ],
            'location_id' => [
                'widget' => 'number',
                'hint' => $model instanceof AvitoService
                    ? self::locationHint($model->location, $model->location_id)
                    : 'ID локации Avito.',
            ],
            'autoload_item_id' => ['widget' => 'text'],
            'city' => ['widget' => 'text'],
            'region' => ['widget' => 'text'],
            'title' => ['widget' => 'text', 'wide' => true],
            'description' => ['widget' => 'textarea', 'rows' => 8, 'wide' => true],
            'price' => ['widget' => 'number'],
            'price_list' => [
                'widget' => 'json',
                'hint' => 'JSON-массив или объект.',
            ],
            'workplace' => ['widget' => 'text'],
            'provider_gender' => ['widget' => 'text'],
            'client_gender' => ['widget' => 'text'],
            'consultation_type' => ['widget' => 'text'],
            'approach' => ['widget' => 'text'],
            'experience' => ['widget' => 'text'],
            'status' => [
                'widget' => 'dropdown',
                'items' => AvitoService::statusOptions(),
            ],
            'avito_item_id' => ['widget' => 'text'],
            'published_at' => ['widget' => 'datetime'],
            'last_error_code' => ['widget' => 'text'],
            'last_error_message' => ['widget' => 'textarea', 'rows' => 3, 'wide' => true],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function accountColumn(): array
    {
        return [
            'attribute' => 'account_id',
            'filter' => Options::accounts(),
            'value' => static function (AvitoService $model, ...$extra): string {
                return Options::accounts()[(int) $model->account_id] ?? (string) $model->account_id;
            },
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

    private static function locationHint(?AvitoLocation $location, mixed $id): string
    {
        if ($location === null) {
            return 'ID из справочника локаций Avito.';
        }

        return 'Сейчас: ' . self::locationName($location, $id);
    }
}
