<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\base\Model;
use yii\base\Security;

/**
 * Форма входа на сайт.
 *
 * Логином является телефон пользователя (таблица {{%user}}, поле phone).
 * Пароль проверяется через User::validatePassword().
 *
 * @property User|null $user
 */
class LoginForm extends Model
{
    /** Логин (телефон) */
    public string $username = '';

    /** Пароль */
    public string $password = '';

    /** Запомнить пользователя */
    public bool $rememberMe = false;

    private ?User $_user = null;

    public function __construct(
        private readonly Security $security,
        $config = [],
    ) {
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            // Обязательные поля
            [['username', 'password'], 'required'],
            // Телефон в качестве логина
            [['username'], 'string', 'max' => 32],
            // Пароль не короче 6 символов (как в модели User)
            [['password'], 'string', 'min' => 6],
            // Запомнить меня
            [['rememberMe'], 'boolean'],
            // Проверка пары логин/пароль
            ['password', 'validatePassword'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'username' => 'Телефон',
            'password' => 'Пароль',
            'rememberMe' => 'Запомнить меня',
        ];
    }

    /**
     * Проверяет корректность пары телефон/пароль.
     */
    public function validatePassword(string $attribute): void
    {
        $user = $this->getUser();

        if ($user === null || !$user->validatePassword($this->password)) {
            $this->addError($attribute, 'Incorrect username or password.');
        }
    }

    /**
     * Выполняет вход в систему.
     *
     * @return bool успешен ли вход
     */
    public function login(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
    }

    /**
     * Возвращает найденного по телефону пользователя или null.
     *
     * Телефон нормализуется к формату хранения (+7XXXXXXXXXX),
     * чтобы вход работал независимо от разделителей и ведущей 8.
     */
    public function getUser(): ?User
    {
        if ($this->_user === null) {
            $this->_user = User::findByPhone($this->normalizePhone($this->username));
        }

        return $this->_user;
    }

    /**
     * Нормализует телефон к виду +7XXXXXXXXXX.
     */
    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        // Ведущая 8 или 9 приводится к 7
        if ($digits[0] === '8' || $digits[0] === '9') {
            $digits = '7' . substr($digits, 1);
        }

        // Если кода страны нет — считаем, что пропущена 7
        if ($digits[0] !== '7') {
            $digits = '7' . $digits;
        }

        return '+' . $digits;
    }
}

