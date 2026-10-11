<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\modules\admin\models\AdminUser;
use app\modules\admin\models\search\AdminUserSearch;
use Yii;
use yii\db\ActiveRecord;

class AdminUserController extends CrudController
{
    /** @var array<int, string> */
    private const STATUSES = [
        AdminUser::STATUS_ACTIVE => 'Активен',
        AdminUser::STATUS_DISABLED => 'Отключён',
    ];

    protected function modelClass(): string
    {
        return AdminUser::class;
    }

    protected function searchModelClass(): string
    {
        return AdminUserSearch::class;
    }

    protected function entityTitle(): string
    {
        return 'Администраторы';
    }

    protected function recordLabel(ActiveRecord $model): string
    {
        return $model instanceof AdminUser ? (string) $model->username : parent::recordLabel($model);
    }

    protected function newModel(): ActiveRecord
    {
        $model = new AdminUser();
        $model->scenario = 'create';
        $model->status = AdminUser::STATUS_ACTIVE;
        $model->role = AdminUser::ROLE_MANAGER;

        return $model;
    }

    protected function deleteBlockedReason(ActiveRecord $model): ?string
    {
        if ((int) $model->getPrimaryKey() === (int) Yii::$app->adminUser->id) {
            return 'Нельзя удалить свою учётную запись.';
        }

        return null;
    }

    protected function gridColumns(): array
    {
        return [
            'id',
            'username',
            [
                'attribute' => 'role',
                'filter' => AdminUser::roleOptions(),
                'value' => static fn (AdminUser $model, ...$extra): string => $model->getRoleLabel(),
            ],
            [
                'attribute' => 'status',
                'filter' => self::STATUSES,
                'value' => static fn (AdminUser $model, ...$extra): string => self::STATUSES[(int) $model->status] ?? (string) $model->status,
            ],
            $this->timeColumn('last_login_at'),
            'last_login_ip',
        ];
    }

    protected function detailAttributes(ActiveRecord $model): array
    {
        return [
            'id',
            'username',
            [
                'attribute' => 'role',
                'value' => static fn (AdminUser $model, ...$extra): string => $model->getRoleLabel(),
            ],
            [
                'attribute' => 'status',
                'value' => static fn (AdminUser $model, ...$extra): string => self::STATUSES[(int) $model->status] ?? (string) $model->status,
            ],
            $this->timeColumn('last_login_at'),
            'last_login_ip',
            $this->timeColumn('created_at'),
            $this->timeColumn('updated_at'),
        ];
    }

    protected function formFields(ActiveRecord $model): array
    {
        return [
            'username' => ['widget' => 'text'],
            'password' => [
                'widget' => 'password',
                'hint' => $model->isNewRecord
                    ? 'Минимум 3 символа.'
                    : 'Оставьте пустым, чтобы не менять пароль.',
            ],
            'role' => [
                'widget' => 'dropdown',
                'items' => AdminUser::roleOptions(),
            ],
            'status' => [
                'widget' => 'dropdown',
                'items' => self::STATUSES,
            ],
        ];
    }
}
