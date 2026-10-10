<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\models\AvitoAccount;
use app\models\AvitoUser;
use app\modules\admin\helpers\Options;
use app\modules\admin\models\search\AvitoUserSearch;
use yii\db\ActiveRecord;

class AvitoUserController extends CrudController
{
    protected function modelClass(): string
    {
        return AvitoUser::class;
    }

    protected function searchModelClass(): string
    {
        return AvitoUserSearch::class;
    }

    protected function entityTitle(): string
    {
        return 'Клиенты Avito';
    }

    protected function recordLabel(ActiveRecord $model): string
    {
        if (!$model instanceof AvitoUser) {
            return parent::recordLabel($model);
        }

        if ($model->name !== null && $model->name !== '') {
            return $model->name;
        }

        return 'user ' . $model->avito_user_id;
    }

    protected function newModel(): ActiveRecord
    {
        $model = new AvitoUser();
        $account = AvitoAccount::findActive();
        if ($account !== null) {
            $model->account_id = $account->id;
        }

        return $model;
    }

    protected function gridColumns(): array
    {
        return [
            'id',
            $this->accountColumn(),
            'avito_user_id',
            'name',
            $this->timeColumn('last_seen_at'),
        ];
    }

    protected function detailAttributes(ActiveRecord $model): array
    {
        return [
            'id',
            $this->accountColumn(),
            'avito_user_id',
            'name',
            'avatar_url:url',
            $this->timeColumn('last_seen_at'),
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
            'avito_user_id' => ['widget' => 'text'],
            'name' => ['widget' => 'text'],
            'avatar_url' => ['widget' => 'text', 'wide' => true],
            'last_seen_at' => ['widget' => 'datetime'],
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
            'value' => static function (AvitoUser $model, ...$extra): string {
                return Options::accounts()[(int) $model->account_id] ?? (string) $model->account_id;
            },
        ];
    }
}
