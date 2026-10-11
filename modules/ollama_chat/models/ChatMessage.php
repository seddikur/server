<?php

declare(strict_types=1);

namespace app\modules\ollama_chat\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Сообщение чата пользователя сайта.
 *
 * @property int $id
 * @property int $user_id
 * @property string $role
 * @property string $content
 * @property int $created_at
 *
 * @property-read \app\modules\users\models\User|null $user
 */
class ChatMessage extends ActiveRecord
{
    public const ROLE_USER = 'user';
    public const ROLE_ASSISTANT = 'assistant';
    public const ROLE_SYSTEM = 'system';

    public static function tableName(): string
    {
        return '{{%ollama_chat_message}}';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['user_id', 'role', 'content'], 'required'],
            [['user_id'], 'integer'],
            [['content'], 'string'],
            [['role'], 'string', 'max' => 20],
            [['role'], 'in', 'range' => [self::ROLE_USER, self::ROLE_ASSISTANT, self::ROLE_SYSTEM]],
        ];
    }

    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(\app\modules\users\models\User::class, ['id' => 'user_id']);
    }

    public static function formatTime(int $timestamp): string
    {
        return Yii::$app->formatter->asDatetime($timestamp, 'php:d.m.Y H:i');
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_USER => 'Пользователь',
            self::ROLE_ASSISTANT => 'AI',
            self::ROLE_SYSTEM => 'Система',
            default => $this->role,
        };
    }
}
