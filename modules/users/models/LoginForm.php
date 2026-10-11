<?php

declare(strict_types=1);

namespace app\modules\users\models;

use Yii;
use yii\base\Model;
use yii\base\Security;

/**
 * Форма входа на сайт (логин — телефон).
 *
 * @property User|null $user
 */
class LoginForm extends Model
{
    public string $username = '';

    public string $password = '';

    public bool $rememberMe = false;

    private ?User $_user = null;

    public function __construct(
        private readonly Security $security,
        $config = [],
    ) {
        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['username', 'password'], 'required'],
            [['username'], 'string', 'max' => 32],
            [['password'], 'string', 'min' => 6],
            [['rememberMe'], 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'username' => 'Телефон',
            'password' => 'Пароль',
            'rememberMe' => 'Запомнить меня',
        ];
    }

    public function validatePassword(string $attribute): void
    {
        $user = $this->getUser();

        if ($user === null || !$user->validatePassword($this->password)) {
            $this->addError($attribute, 'Incorrect username or password.');
        }
    }

    public function login(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
    }

    public function getUser(): ?User
    {
        if ($this->_user === null) {
            $this->_user = User::findByPhone($this->normalizePhone($this->username));
        }

        return $this->_user;
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        if ($digits[0] === '8' || $digits[0] === '9') {
            $digits = '7' . substr($digits, 1);
        }

        if ($digits[0] !== '7') {
            $digits = '7' . $digits;
        }

        return '+' . $digits;
    }
}
