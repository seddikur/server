<?php

declare(strict_types=1);

namespace app\modules\admin\models;

use app\models\AdminUser;
use Yii;
use yii\base\Model;

/**
 * Форма входа в админ-панель.
 *
 * Логином является поле admin_users.username.
 * При успешном входе пишется запись в admin_login_log.
 */
class AdminLoginForm extends Model
{
    /** Логин администратора */
    public string $username = '';

    /** Пароль */
    public string $password = '';

    /** Запомнить администратора */
    public bool $rememberMe = false;

    private ?AdminUser $_user = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['username', 'password'], 'required'],
            [['username'], 'string', 'max' => 64],
            [['password'], 'string', 'min' => 3],
            [['rememberMe'], 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'username' => 'Логин',
            'password' => 'Пароль',
            'rememberMe' => 'Запомнить меня',
        ];
    }

    /**
     * Проверяет пару логин/пароль и доступ к панели.
     */
    public function validatePassword(string $attribute): void
    {
        $user = $this->getUser();

        if ($user === null || !$user->validatePassword($this->password)) {
            $this->addError($attribute, 'Неверный логин или пароль.');
            return;
        }

        if (!$user->canAccessAdmin()) {
            $this->addError($attribute, 'Доступ к админ-панели запрещён.');
        }
    }

    /**
     * Выполняет вход администратора.
     *
     * @return bool успешен ли вход
     */
    public function login(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->getUser();
        $duration = $this->rememberMe ? 3600 * 24 * 30 : 0;

        if (!Yii::$app->adminUser->login($user, $duration)) {
            return false;
        }

        // Фиксируем факт входа
        $user->recordLogin(Yii::$app->request->userIP, Yii::$app->request->userAgent);

        return true;
    }

    /**
     * Возвращает найденного администратора или null.
     */
    public function getUser(): ?AdminUser
    {
        if ($this->_user === null) {
            $this->_user = AdminUser::findByUsername($this->username);
        }

        return $this->_user;
    }
}
