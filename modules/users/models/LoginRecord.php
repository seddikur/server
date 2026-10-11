<?php

declare(strict_types=1);

namespace app\modules\users\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Журнал входов пользователей сайта ({{%login_record}}).
 *
 * @property int $id
 * @property string $username
 * @property string|null $clientIp
 * @property string $created_at
 */
class LoginRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%login_record}}';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['username'], 'required'],
            [['username'], 'string'],
            [['clientIp'], 'string', 'max' => 64],
            [['created_at'], 'safe'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'username' => 'Логин',
            'clientIp' => 'IP адрес',
            'created_at' => 'Дата и время',
        ];
    }
}
