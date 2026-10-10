<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Администратор панели (таблица {{%admin_users}}).
 *
 * @property int $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property int $role
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property int|null $last_login_at
 * @property string|null $last_login_ip
 */
class AdminUser extends ActiveRecord implements IdentityInterface
{
    public const STATUS_DISABLED = 0;
    public const STATUS_ACTIVE = 10;

    public const ROLE_MANAGER = 50;
    public const ROLE_ADMIN = 100;

    /** @var string|null Виртуальное поле для формы */
    public $password;

    public static function tableName(): string
    {
        return '{{%admin_users}}';
    }

    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert && ($this->auth_key === null || $this->auth_key === '')) {
            $this->generateAuthKey();
        }

        if (is_string($this->password) && $this->password !== '') {
            $this->setPassword($this->password);
            $this->password = null;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            [['username', 'role'], 'required'],
            [['username'], 'string', 'max' => 64],
            [['username'], 'unique'],
            [['role'], 'integer'],
            [['role'], 'in', 'range' => [self::ROLE_MANAGER, self::ROLE_ADMIN]],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DISABLED]],
            ['password', 'string', 'min' => 3],
            ['password', 'required', 'on' => 'create'],
        ];
    }

    public function scenarios(): array
    {
        $scenarios = parent::scenarios();
        $scenarios['create'] = $scenarios[self::SCENARIO_DEFAULT];

        return $scenarios;
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'username' => 'Логин',
            'password' => 'Пароль',
            'role' => 'Роль',
            'status' => 'Статус',
            'created_at' => 'Создан',
            'updated_at' => 'Обновлён',
            'last_login_at' => 'Последний вход',
            'last_login_ip' => 'IP последнего входа',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function roleOptions(): array
    {
        $roles = Yii::$app->params['adminRoles']['roles'] ?? [];
        $options = [];
        foreach ($roles as $id => $meta) {
            $options[(int) $id] = (string) ($meta['label'] ?? $id);
        }

        return $options;
    }

    public function getRoleLabel(): string
    {
        $roles = Yii::$app->params['adminRoles']['roles'] ?? [];

        return $roles[$this->role]['label'] ?? '—';
    }

    public function isAdmin(): bool
    {
        return (int) $this->role === self::ROLE_ADMIN;
    }

    public function isManager(): bool
    {
        return (int) $this->role === self::ROLE_MANAGER;
    }

    public function canAccessAdmin(): bool
    {
        return $this->isAdmin() || $this->isManager();
    }

    public static function findIdentity($id): ?IdentityInterface
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    public static function findByUsername(string $username): ?self
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey(): ?string
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey): bool
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function recordLogin(?string $ip = null, ?string $userAgent = null): void
    {
        $now = time();
        $ip = $ip !== null && $ip !== '' ? mb_substr($ip, 0, 45) : null;
        $userAgent = $userAgent !== null && $userAgent !== '' ? mb_substr($userAgent, 0, 512) : null;

        $this->updateAttributes([
            'last_login_at' => $now,
            'last_login_ip' => $ip,
        ]);

        $log = new AdminLoginLog();
        $log->admin_user_id = (int) $this->id;
        $log->username = (string) $this->username;
        $log->ip = $ip;
        $log->user_agent = $userAgent;
        $log->created_at = $now;
        $log->save(false);
    }
}
