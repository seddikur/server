<?php

declare(strict_types=1);

namespace app\modules\admin\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int|null $admin_user_id
 * @property string $username
 * @property string|null $ip
 * @property string|null $user_agent
 * @property int $created_at
 * @property-read AdminUser|null $adminUser
 */
class AdminLoginLog extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%admin_login_log}}';
    }

    public function rules(): array
    {
        return [
            [['username', 'created_at'], 'required'],
            [['admin_user_id', 'created_at'], 'integer'],
            [['username'], 'string', 'max' => 64],
            [['ip'], 'string', 'max' => 45],
            [['user_agent'], 'string', 'max' => 512],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'admin_user_id' => 'Админ',
            'username' => 'Логин',
            'ip' => 'IP',
            'user_agent' => 'User-Agent',
            'created_at' => 'Время входа',
        ];
    }

    public function getAdminUser()
    {
        return $this->hasOne(AdminUser::class, ['id' => 'admin_user_id']);
    }
}
