<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\models\AvitoToken;
use app\modules\admin\helpers\Format;
use app\modules\admin\models\search\AvitoTokenSearch;
use yii\db\ActiveRecord;
use yii\helpers\Html;

class AvitoTokenController extends CrudController
{
    /** @var array<string, string> */
    private const FLOWS = [
        AvitoToken::FLOW_CLIENT_CREDENTIALS => 'Client credentials',
        AvitoToken::FLOW_AUTHORIZATION_CODE => 'Код авторизации',
    ];

    protected function modelClass(): string
    {
        return AvitoToken::class;
    }

    protected function searchModelClass(): string
    {
        return AvitoTokenSearch::class;
    }

    protected function entityTitle(): string
    {
        return 'Токены Avito';
    }

    protected function recordLabel(ActiveRecord $model): string
    {
        return $model instanceof AvitoToken ? (string) $model->account_id : parent::recordLabel($model);
    }

    protected function newModel(): ActiveRecord
    {
        $model = new AvitoToken();
        $model->oauth_flow = AvitoToken::FLOW_CLIENT_CREDENTIALS;
        $model->expires_at = time() + 3600;

        return $model;
    }

    protected function gridColumns(): array
    {
        return [
            'id',
            'account_id',
            [
                'attribute' => 'access_token',
                'filter' => false,
                'value' => static fn (AvitoToken $model, ...$extra): string => Format::mask($model->access_token),
            ],
            [
                'attribute' => 'oauth_flow',
                'filter' => self::FLOWS,
                'value' => static fn (AvitoToken $model, ...$extra): string => self::FLOWS[$model->oauth_flow] ?? (string) $model->oauth_flow,
            ],
            $this->timeColumn('expires_at'),
            [
                'label' => 'Истёк',
                'value' => static fn (AvitoToken $model, ...$extra): string => $model->isExpired() ? 'Да' : 'Нет',
            ],
        ];
    }

    protected function detailAttributes(ActiveRecord $model): array
    {
        return [
            'id',
            'account_id',
            [
                'attribute' => 'access_token',
                'format' => 'raw',
                'value' => static fn (AvitoToken $model, ...$extra): string => self::secret($model->access_token),
            ],
            [
                'attribute' => 'refresh_token',
                'format' => 'raw',
                'value' => static fn (AvitoToken $model, ...$extra): string => self::secret($model->refresh_token),
            ],
            'scope',
            [
                'attribute' => 'oauth_flow',
                'value' => static fn (AvitoToken $model, ...$extra): string => self::FLOWS[$model->oauth_flow] ?? (string) $model->oauth_flow,
            ],
            $this->timeColumn('expires_at'),
            $this->timeColumn('created_at'),
            $this->timeColumn('updated_at'),
        ];
    }

    protected function formFields(ActiveRecord $model): array
    {
        return [
            'account_id' => [
                'widget' => 'text',
                'hint' => 'Строковый идентификатор аккаунта, например default.',
            ],
            'access_token' => ['widget' => 'textarea', 'rows' => 4, 'wide' => true],
            'refresh_token' => ['widget' => 'textarea', 'rows' => 4, 'wide' => true],
            'expires_at' => ['widget' => 'datetime'],
            'scope' => ['widget' => 'text'],
            'oauth_flow' => [
                'widget' => 'dropdown',
                'items' => self::FLOWS,
            ],
        ];
    }

    private static function secret(?string $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return Html::tag('code', Html::encode($value), ['class' => 'text-break']);
    }
}
