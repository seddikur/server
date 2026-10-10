<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\models\AvitoMessage;
use app\modules\admin\helpers\Format;
use app\modules\admin\models\search\AvitoMessageSearch;
use yii\db\ActiveRecord;
use yii\helpers\Html;

class AvitoMessageController extends CrudController
{
    /** @var array<string, string> */
    private const DIRECTIONS = [
        AvitoMessage::DIRECTION_IN => 'Входящее',
        AvitoMessage::DIRECTION_OUT => 'Исходящее',
    ];

    /** @var array<string, string> */
    private const AUTHORS = [
        AvitoMessage::AUTHOR_USER => 'Клиент',
        AvitoMessage::AUTHOR_EMPLOYEE => 'Сотрудник',
        AvitoMessage::AUTHOR_SYSTEM => 'Система',
    ];

    protected function modelClass(): string
    {
        return AvitoMessage::class;
    }

    protected function searchModelClass(): string
    {
        return AvitoMessageSearch::class;
    }

    protected function entityTitle(): string
    {
        return 'Сообщения Avito';
    }

    protected function newModel(): ActiveRecord
    {
        $model = new AvitoMessage();
        $model->direction = AvitoMessage::DIRECTION_IN;
        $model->author_type = AvitoMessage::AUTHOR_USER;
        $model->content_type = 'text';
        $model->is_read = false;
        $model->sent_at = time();

        return $model;
    }

    protected function beforePersist(ActiveRecord $model): void
    {
        if (!$model instanceof AvitoMessage) {
            return;
        }

        if ($model->direction === null || $model->direction === '') {
            $model->direction = AvitoMessage::DIRECTION_IN;
        }
        if ($model->author_type === null || $model->author_type === '') {
            $model->author_type = AvitoMessage::AUTHOR_USER;
        }
        if ($model->content_type === null || $model->content_type === '') {
            $model->content_type = 'text';
        }
        if ($model->is_read === null) {
            $model->is_read = false;
        }
        if ($model->isNewRecord && ($model->created_at === null || $model->created_at === '')) {
            $model->created_at = time();
        }
    }

    protected function gridColumns(): array
    {
        return [
            'id',
            [
                'attribute' => 'chat_id',
                'value' => static fn (AvitoMessage $model, ...$extra): string => $model->chat->avito_chat_id ?? (string) $model->chat_id,
            ],
            [
                'attribute' => 'direction',
                'filter' => self::DIRECTIONS,
                'value' => static fn (AvitoMessage $model, ...$extra): string => self::DIRECTIONS[$model->direction] ?? (string) $model->direction,
            ],
            [
                'attribute' => 'author_type',
                'filter' => self::AUTHORS,
                'value' => static fn (AvitoMessage $model, ...$extra): string => self::AUTHORS[$model->author_type] ?? (string) $model->author_type,
            ],
            [
                'attribute' => 'body',
                'value' => static fn (AvitoMessage $model, ...$extra): string => Format::excerpt($model->body, 80),
            ],
            [
                'attribute' => 'is_read',
                'format' => 'boolean',
                'filter' => ['1' => 'Да', '0' => 'Нет'],
            ],
            $this->timeColumn('sent_at'),
        ];
    }

    protected function detailAttributes(ActiveRecord $model): array
    {
        return [
            'id',
            [
                'attribute' => 'chat_id',
                'value' => static function (AvitoMessage $model, ...$extra): string {
                    $chat = $model->chat;
                    if ($chat === null) {
                        return (string) $model->chat_id;
                    }

                    return $chat->avito_chat_id . ' (#' . $chat->id . ')';
                },
            ],
            'avito_message_id',
            [
                'attribute' => 'direction',
                'value' => static fn (AvitoMessage $model, ...$extra): string => self::DIRECTIONS[$model->direction] ?? (string) $model->direction,
            ],
            [
                'attribute' => 'author_type',
                'value' => static fn (AvitoMessage $model, ...$extra): string => self::AUTHORS[$model->author_type] ?? (string) $model->author_type,
            ],
            'author_id',
            'body:ntext',
            'content_type',
            [
                'attribute' => 'attachments',
                'format' => 'raw',
                'value' => static function (AvitoMessage $model, ...$extra): string {
                    $text = Format::jsonText($model->attachments);
                    if ($text === '') {
                        return '—';
                    }

                    return Html::tag('pre', Html::encode($text), ['class' => 'mb-0 small']);
                },
            ],
            'is_read:boolean',
            $this->timeColumn('sent_at'),
            $this->timeColumn('created_at'),
        ];
    }

    protected function formFields(ActiveRecord $model): array
    {
        $hint = 'ID чата в нашей базе.';
        if ($model instanceof AvitoMessage && $model->chat !== null) {
            $hint = 'Сейчас: ' . $model->chat->avito_chat_id;
        }

        return [
            'chat_id' => ['widget' => 'number', 'hint' => $hint],
            'avito_message_id' => ['widget' => 'text'],
            'direction' => ['widget' => 'dropdown', 'items' => self::DIRECTIONS],
            'author_type' => ['widget' => 'dropdown', 'items' => self::AUTHORS],
            'author_id' => ['widget' => 'text'],
            'body' => ['widget' => 'textarea', 'rows' => 5, 'wide' => true],
            'content_type' => ['widget' => 'text'],
            'attachments' => [
                'widget' => 'json',
                'hint' => 'JSON-массив или объект. Пустое поле очищает вложения.',
            ],
            'is_read' => ['widget' => 'checkbox'],
            'sent_at' => ['widget' => 'datetime'],
        ];
    }
}
