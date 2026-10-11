# Модуль `setting`

Хранение настроек сайта в таблице `settings` (ключ — значение) и CRUD в админке.

## Структура

```
modules/setting/
├── Module.php
├── NavItem.php
├── README.md
├── migrations/
│   └── m260810_215801_create_settings_table.php
├── models/
│   ├── Setting.php
│   └── search/
│       ├── SettingSearch.php
│       └── SearchTrait.php
└── controllers/
    └── SettingsController.php
```

## Подключение

1. Скопируйте каталог `modules/setting`.

2. Миграция таблицы — файл в `migrations/` модуля. В приложении вызовите её из своей миграции:

```php
require_once __DIR__ . '/../modules/setting/migrations/m260810_215801_create_settings_table.php';
(new m260810_215801_create_settings_table(['db' => $this->db]))->safeUp();
```

3. Модуль в конфиге:

```php
'admin' => [
    'modules' => [
        'setting' => ['class' => \app\modules\setting\Module::class],
    ],
],
```

4. URL (короткий путь):

```php
'admin/settings' => 'admin/setting/settings/index',
'admin/settings/<action:\w+>' => 'admin/setting/settings/<action>',
'admin/settings/<action:\w+>/<id:\d+>' => 'admin/setting/settings/<action>',
```

5. Меню: `NavItem::forAdmin()`.

## API модели

- `Setting::getValue($key, $default)` — чтение из БД
- `Setting::setValue($key, $value, $description?)` — запись
- Константы `Setting::KEY_*` — известные ключи

## Зависимости

CRUD-страницы используют `app\modules\admin\controllers\CrudController` и общие view `@app/modules/admin/views/crud/*` — нужна оболочка админ-модуля (или замените базовый класс контроллера в своём проекте).
