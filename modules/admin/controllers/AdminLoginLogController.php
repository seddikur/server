<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\models\AdminLoginLog;
use app\modules\admin\helpers\Format;
use app\modules\admin\helpers\Options;
use app\modules\admin\models\search\AdminLoginLogSearch;
use yii\db\ActiveRecord;

class AdminLoginLogController extends CrudController
{
    protected function modelClass(): string
    {
        return AdminLoginLog::class;
    }

    protected function searchModelClass(): string
    {
        return AdminLoginLogSearch::class;
    }

    protected function entityTitle(): string
    {
        return 'Журнал входов';
    }

    protected function recordLabel(ActiveRecord $model): string
    {
        if (!$model instanceof AdminLoginLog) {
            return parent::recordLabel($model);
        }

        return $model->username . ' #' . $model->id;
    }

    protected function newModel(): ActiveRecord
    {
        $model = new AdminLoginLog();
        $model->created_at = time();

        return $model;
    }

    protected function gridColumns(): array
    {
        return [
            'id',
            [
                'attribute' => 'admin_user_id',
                'filter' => Options::admins(),
                'value' => static function (AdminLoginLog $model, ...$extra): string {
                    return $model->adminUser->username ?? '—';
                },
            ],
            'username',
            'ip',
            [
                'attribute' => 'user_agent',
                'value' => static fn (AdminLoginLog $model, ...$extra): string => Format::excerpt($model->user_agent, 60),
            ],
            $this->timeColumn('created_at'),
        ];
    }

    protected function detailAttributes(ActiveRecord $model): array
    {
        return [
            'id',
            [
                'attribute' => 'admin_user_id',
                'value' => static fn (AdminLoginLog $model, ...$extra): string => $model->adminUser->username ?? '—',
            ],
            'username',
            'ip',
            'user_agent:ntext',
            $this->timeColumn('created_at'),
        ];
    }

    protected function formFields(ActiveRecord $model): array
    {
        return [
            'admin_user_id' => [
                'widget' => 'dropdown',
                'items' => Options::admins(),
                'prompt' => '—',
                'hint' => 'Можно оставить пустым, если учётка уже удалена.',
            ],
            'username' => ['widget' => 'text'],
            'ip' => ['widget' => 'text'],
            'user_agent' => ['widget' => 'textarea', 'rows' => 3, 'wide' => true],
            'created_at' => ['widget' => 'datetime'],
        ];
    }
}
