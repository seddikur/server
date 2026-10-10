<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Клиент сайта (таблица {{%user}}).
 *
 * @property int $id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string $password_hash
 * @property string|null $email
 * @property string|null $phone
 * @property string $auth_key
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 */
class User extends ActiveRecord implements IdentityInterface
{
    public const STATUS_DELETED = 0;
    public const STATUS_INACTIVE = 9;
    public const STATUS_ACTIVE = 10;
    public const STATUS_PENDING = 11;

    /** @var string|null Виртуальное поле для формы создания */
    public $password;

    public static function tableName(): string
    {
        return '{{%user}}';
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

        // Если задан виртуальный пароль — хешируем его в password_hash
        if (is_string($this->password) && $this->password !== '') {
            $this->setPassword($this->password);
            $this->password = null;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            [['phone'], 'required'],
            [['first_name', 'last_name'], 'string', 'max' => 64],
            [['email'], 'string', 'max' => 128],
            [['email'], 'email'],
            [['phone'], 'string', 'max' => 32],
            [['email', 'phone'], 'unique'],
            [['status'], 'integer'],
            ['status', 'default', 'value' => self::STATUS_PENDING],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED, self::STATUS_PENDING]],
            ['password_hash', 'safe'],
            ['password', 'string', 'min' => 6],
            ['password', 'required', 'on' => 'create'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'phone' => 'Телефон',
            'email' => 'Email',
            'first_name' => 'Имя',
            'last_name' => 'Фамилия',
            'password' => 'Пароль',
            'status' => 'Статус',
            'created_at' => 'Создан',
            'updated_at' => 'Обновлён',
        ];
    }

    public function scenarios(): array
    {
        $scenarios = parent::scenarios();
        $scenarios['create'] = $scenarios[self::SCENARIO_DEFAULT];
        return $scenarios;
    }

    public static function findIdentity($id): ?IdentityInterface
    {
        return static::findOne([
            'id' => $id,
            'status' => [self::STATUS_ACTIVE, self::STATUS_PENDING],
        ]);
    }

    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    public static function findByUsername(string $username): ?self
    {
        return static::findOne(['phone' => $username]);
    }

    public static function findByPhone(string $phone): ?self
    {
        return static::findOne(['phone' => $phone]);
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
        if ($this->password_hash === null || $this->password_hash === '') {
            return false;
        }

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

    public function getFullName(): string
    {
        $name = trim((string) $this->first_name . ' ' . (string) $this->last_name);

        return $name !== '' ? $name : (string) ($this->phone ?: '');
    }

    public function isAdmin(): bool
    {
        return Yii::$app->authManager->checkAccess($this->id, 'admin');
    }

    public function isManager(): bool
    {
        return Yii::$app->authManager->checkAccess($this->id, 'manager');
    }
}

