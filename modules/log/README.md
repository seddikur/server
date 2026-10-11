# Модуль `log`

Просмотр хвоста файлов логов Yii2 (`yii\log\FileTarget`) и очистка выбранного файла. Самодостаточный пакет: контроллер, представление, стили.

## Структура

```
modules/log/
├── Module.php              # настройки layout и авторизации
├── NavItem.php             # пункт меню для админки
├── README.md
├── assets/
│   ├── LogAsset.php
│   └── log.css
├── controllers/
│   ├── SecuredController.php
│   └── LogsController.php
└── views/logs/
    └── index.php
```

## Подключение

1. Скопируйте каталог `modules/log` в проект.

2. Зарегистрируйте модуль (вложенный в админку):

```php
'modules' => [
    'admin' => [
        'class' => \app\modules\admin\Module::class,
        'modules' => [
            'log' => [
                'class' => \app\modules\log\Module::class,
                'layout' => '@app/modules/admin/views/layouts/main',
                'userComponent' => 'adminUser',
            ],
        ],
    ],
],
```

3. Маршруты (опционально, короткий URL):

```php
'admin/logs' => 'admin/log/logs/index',
'admin/logs/<action:\w+>' => 'admin/log/logs/<action>',
```

4. Пункт меню: `NavItem::forAdmin()` или URL `/admin/log/logs/index`.

5. В конфиге `log` должны быть `FileTarget` с путями в `@runtime/logs/*.log`.

## Настройки `Module`

| Свойство | По умолчанию | Описание |
|----------|--------------|----------|
| `layout` | layout админки | Обёртка страницы |
| `userComponent` | `adminUser` | Компонент `yii\web\User` |
| `accessCheck` | `null` | `callable(identity): bool` для RBAC |

Identity с методом `canAccessAdmin()` проверяется автоматически, если он есть.

## Экшены

- `GET …/logs/index` — просмотр (фильтры: `log`, `lines`, `light`)
- `POST …/logs/clear` — очистка файла
