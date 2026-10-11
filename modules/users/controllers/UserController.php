<?php

declare(strict_types=1);

namespace app\modules\users\controllers;

use app\modules\admin\controllers\CrudController;
use app\modules\users\models\User;
use app\modules\users\models\search\UserSearch;
use yii\db\ActiveRecord;
use yii\validators\Validator;

class UserController extends CrudController
{
    /** @var array<int, string> */
    private const STATUSES = [
        User::STATUS_ACTIVE => 'Активен',
        User::STATUS_PENDING => 'Ожидает',
        User::STATUS_INACTIVE => 'Неактивен',
        User::STATUS_DELETED => 'Удалён',
    ];

    protected function modelClass(): string
    {
        return User::class;
    }

    protected function searchModelClass(): string
    {
        return UserSearch::class;
    }

    protected function entityTitle(): string
    {
        return 'Пользователи';
    }

    protected function recordLabel(ActiveRecord $model): string
    {
        return $model instanceof User ? $model->getFullName() : parent::recordLabel($model);
    }

    protected function newModel(): ActiveRecord
    {
        $model = new User();
        $model->scenario = 'create';
        $model->status = User::STATUS_ACTIVE;

        return $model;
    }

    protected function prepare(ActiveRecord $model): void
    {
        parent::prepare($model);
        if (!$model instanceof User) {
            return;
        }

        $model->validators->append(Validator::createValidator(
            'required',
            $model,
            ['first_name', 'last_name', 'email'],
        ));
        $model->on(ActiveRecord::EVENT_BEFORE_VALIDATE, static function ($event) use ($model): void {
            if (!$model->isNewRecord) {
                $model->password_hash = $model->getOldAttribute('password_hash');
            }
        });
    }

    protected function gridColumns(): array
    {
        return [
            'id',
            'phone',
            'email',
            'first_name',
            'last_name',
            [
                'attribute' => 'status',
                'filter' => self::STATUSES,
                'value' => static fn (User $model, ...$extra): string => self::STATUSES[(int) $model->status] ?? (string) $model->status,
            ],
            $this->timeColumn('created_at'),
        ];
    }

    protected function detailAttributes(ActiveRecord $model): array
    {
        return [
            'id',
            'phone',
            'email',
            'first_name',
            'last_name',
            [
                'attribute' => 'status',
                'value' => static fn (User $model, ...$extra): string => self::STATUSES[(int) $model->status] ?? (string) $model->status,
            ],
            $this->timeColumn('created_at'),
            $this->timeColumn('updated_at'),
        ];
    }

    protected function formFields(ActiveRecord $model): array
    {
        return [
            'phone' => ['widget' => 'phone'],
            'email' => ['widget' => 'text'],
            'first_name' => ['widget' => 'text'],
            'last_name' => ['widget' => 'text'],
            'password' => [
                'widget' => 'password',
                'hint' => $model->isNewRecord
                    ? 'Минимум 6 символов.'
                    : 'Оставьте пустым, чтобы не менять пароль.',
            ],
            'status' => [
                'widget' => 'dropdown',
                'items' => self::STATUSES,
            ],
        ];
    }
}
