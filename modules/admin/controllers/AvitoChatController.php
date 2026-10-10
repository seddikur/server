<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\models\AvitoAccount;
use app\models\AvitoChat;
use app\modules\admin\helpers\Options;
use app\modules\admin\models\search\AvitoChatSearch;
use yii\db\ActiveRecord;

class AvitoChatController extends CrudController
{
    protected function modelClass(): string
    {
        return AvitoChat::class;
    }

    protected function searchModelClass(): string
    {
        return AvitoChatSearch::class;
    }

    protected function entityTitle(): string
    {
        return 'Чаты Avito';
    }

    protected function recordLabel(ActiveRecord $model): string
    {
        return $model instanceof AvitoChat ? (string) $model->avito_chat_id : parent::recordLabel($model);
    }

    protected function newModel(): ActiveRecord
    {
        $model = new AvitoChat();
        $model->unread_count = 0;
        $account = AvitoAccount::findActive();
        if ($account !== null) {
            $model->account_id = $account->id;
        }

        return $model;
    }

    protected function beforePersist(ActiveRecord $model): void
    {
        if ($model instanceof AvitoChat && $model->unread_count === null) {
            $model->unread_count = 0;
        }
    }

    protected function gridColumns(): array
    {
        return [
            'id',
            $this->accountColumn(),
            'avito_chat_id',
            [
                'attribute' => 'user_id',
                'value' => static function (AvitoChat $model, ...$extra): string {
                    $user = $model->user;
                    if ($user === null) {
                        return (string) $model->user_id;
                    }

                    return ($user->name !== null && $user->name !== '')
                        ? $user->name
                        : (string) $user->avito_user_id;
                },
            ],
            [
                'attribute' => 'service_id',
                'value' => static fn (AvitoChat $model, ...$extra): string => $model->service->title ?? '—',
            ],
            'unread_count',
            $this->timeColumn('last_message_at'),
        ];
    }

    protected function detailAttributes(ActiveRecord $model): array
    {
        return [
            'id',
            $this->accountColumn(),
            'avito_chat_id',
            [
                'attribute' => 'user_id',
                'value' => static function (AvitoChat $model, ...$extra): string {
                    $user = $model->user;
                    if ($user === null) {
                        return (string) $model->user_id;
                    }

                    $name = ($user->name !== null && $user->name !== '') ? $user->name : (string) $user->avito_user_id;

                    return $name . ' (#' . $user->id . ')';
                },
            ],
            'avito_item_id',
            [
                'attribute' => 'service_id',
                'value' => static function (AvitoChat $model, ...$extra): string {
                    if ($model->service === null) {
                        return $model->service_id === null ? '—' : (string) $model->service_id;
                    }

                    return $model->service->title . ' (#' . $model->service->id . ')';
                },
            ],
            'unread_count',
            $this->timeColumn('last_message_at'),
            $this->timeColumn('created_at'),
            $this->timeColumn('updated_at'),
        ];
    }

    protected function formFields(ActiveRecord $model): array
    {
        $serviceId = $model instanceof AvitoChat && $model->service_id !== null
            ? (int) $model->service_id
            : null;

        return [
            'account_id' => [
                'widget' => 'dropdown',
                'items' => Options::accounts(),
                'prompt' => 'Выберите аккаунт',
            ],
            'avito_chat_id' => ['widget' => 'text'],
            'user_id' => [
                'widget' => 'number',
                'hint' => $this->userHint($model),
            ],
            'avito_item_id' => ['widget' => 'text'],
            'service_id' => [
                'widget' => 'dropdown',
                'items' => Options::services($serviceId),
                'prompt' => '—',
                'hint' => 'В списке последние 500 услуг.',
            ],
            'unread_count' => ['widget' => 'number'],
            'last_message_at' => ['widget' => 'datetime'],
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
            'value' => static function (AvitoChat $model, ...$extra): string {
                return Options::accounts()[(int) $model->account_id] ?? (string) $model->account_id;
            },
        ];
    }

    private function userHint(ActiveRecord $model): string
    {
        if (!$model instanceof AvitoChat || $model->user === null) {
            return 'ID записи клиента Avito в нашей базе.';
        }

        $user = $model->user;
        $name = ($user->name !== null && $user->name !== '') ? $user->name : (string) $user->avito_user_id;

        return 'Сейчас: ' . $name;
    }
}
