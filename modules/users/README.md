# Модуль `users`

Пользователи сайта: таблицы `user` и `login_record`, identity для компонента `user`, форма входа, CRUD в админке, консольный сид.

## Структура

```
modules/users/
├── Module.php
├── NavItem.php
├── README.md
├── migrations/
│   ├── m241218_112333_create_login_record_table.php
│   └── m250509_000002_create_user_table.php
├── models/
│   ├── User.php
│   ├── LoginForm.php
│   ├── LoginRecord.php
│   └── search/
├── controllers/
│   └── UserController.php      # CRUD в админке
└── commands/
    └── UserController.php      # php yii user/init
```

## Подключение

1. Скопируйте `modules/users`.

2. Миграции в корне приложения делегируют модулю (см. `migrations/m241218_*`, `migrations/m250509_*`).

3. `config/web.php`:

```php
'admin' => [
    'modules' => [
        'users' => ['class' => \app\modules\users\Module::class],
    ],
],
'components' => [
    'user' => [
        'identityClass' => \app\modules\users\models\User::class,
    ],
],
```

4. URL админки:

```php
'admin/user' => 'admin/users/user/index',
'admin/user/<action:\w+>/<id:\d+>' => 'admin/users/user/<action>',
'admin/user/<action:\w+>' => 'admin/users/user/<action>',
```

5. Консоль (`config/console.php`):

```php
'controllerMap' => [
    'user' => \app\modules\users\commands\UserController::class,
],
```

6. Меню: `NavItem::forAdmin()`.

## Сиды

После `php yii migrate` и `php yii rbac/init`:

```bash
php yii user/init
```

Создаёт тестовых пользователей с ролями RBAC (см. корневой README).

## Зависимости

- CRUD: `app\modules\admin\controllers\CrudController`, views `@app/modules/admin/views/crud/*`
- RBAC роли `user`, `moder`, `admin` — `rbac/` и команда `rbac/init`
