# Модуль `admin`

Оболочка админ-панели: отдельный вход, layout, CRUD, учётные записи администраторов, журнал входов, роли.

## Структура

```
modules/admin/
├── Module.php
├── Nav.php
├── README.md
├── config/
│   └── roles.php              # роли и permissions
├── migrations/
│   └── m260802_160001_create_admin_users_table.php
├── assets/
│   ├── AdminAsset.php
│   ├── css/admin.css
│   └── js/admin.js
├── models/
│   ├── AdminUser.php          # identity для компонента adminUser
│   ├── AdminLoginLog.php
│   ├── AdminLoginForm.php
│   └── search/
├── controllers/
├── views/
│   ├── layouts/               # main, sidebar
│   ├── default/               # login, dashboard
│   └── crud/
└── helpers/
```

## Подключение

1. Скопируйте `modules/admin`.

2. Миграция (из корня приложения):

```php
// migrations/m260802_160000_create_admin_users_table.php
require_once __DIR__ . '/../modules/admin/migrations/m260802_160001_create_admin_users_table.php';
(new m260802_160001_create_admin_users_table(['db' => $this->db]))->safeUp();
```

Сид: пользователь `admin` / `admin` (роль 100).

3. `config/web.php`:

```php
$params['adminRoles'] = require __DIR__ . '/../modules/admin/config/roles.php';

'modules' => [
    'admin' => [
        'class' => \app\modules\admin\Module::class,
        'defaultRoute' => 'default',
        'modules' => [ /* log, setting, … */ ],
    ],
],
'components' => [
    'adminUser' => [
        'class' => \yii\web\User::class,
        'identityClass' => \app\modules\admin\models\AdminUser::class,
        'loginUrl' => ['/admin/login'],
        // …
    ],
],
```

4. Маршруты: `admin`, `admin/login`, `admin/<controller>/<action>`.

## Роли (`config/roles.php`)

| ID | Код | Описание |
|----|-----|----------|
| 100 | admin | Полный доступ (`*`) |
| 50 | manager | Ограниченный набор permissions |

Права задаются строками `{controller}.{action}` (см. массив `permissions`). Проверку по RBAC можно повесить на `AdminController::beforeAction`.

## Вложенные модули

Подключаются в `'admin' => ['modules' => …]` (например `log`, `setting`). Пункты меню — через `Nav.php` и `NavItem::forAdmin()` дочерних модулей.
