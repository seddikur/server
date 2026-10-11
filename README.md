## Модули

| Каталог | Назначение |
|---------|------------|
| `modules/admin/` | Админ-панель: layout, AdminUser, роли, миграции — [README](modules/admin/README.md) |
| `modules/log/` | Просмотр и очистка файлов логов — [README](modules/log/README.md) |
| `modules/setting/` | Настройки сайта (ключ — значение) — [README](modules/setting/README.md) |
| `modules/users/` | Пользователи сайта, вход, login_record — [README](modules/users/README.md) |

Подключаемые модули регистрируются вложенными в `admin` (`config/web.php`).

## Cron

Фоновые задачи: диспетчер `cron_manager.sh` + скрипты в `cron_scripts/`.
В системном crontab — одна строка. Подробности: [docs/cron.md](docs/cron.md). Логи: `/admin/cron`.

## Порядок установки

1. Запуск Docker: `docker compose up -d`
2. Зависимости: `docker compose run --rm composer install --ignore-platform-reqs`
3. В контейнер: `docker compose exec -it web bash`
4. Composer: `composer install`
5. Миграции: `php yii migrate`
6. RBAC (пользователи сайта): `php yii rbac/init`
7. Пользователи сайта (опционально): `php yii user/init`

> Для журнала входов пользователей сайта также нужна миграция `login_record` (входит в `php yii migrate`).

## Учётные записи

Два независимых контура входа: **сайт** (`/login`) и **админ-панель** (`/admin/login`).

### Админ-панель (`admin_users`)

Создаётся автоматически при миграции `m260802_160000_create_admin_users_table`.

| Логин | Пароль | Роль |
|-------|--------|------|
| `admin` | `admin` | Администратор |

URL: http://localhost:8080/admin/login

### Пользователи сайта (`user`)

Создаются командой `php yii user/init` (после `php yii rbac/init`).

| Логин | Пароль | Роль RBAC |
|-------|--------|-----------|
| `manager@example.com` / `+79999999998` | `manager` | moder |
| `user@example.com` / `+79999999997` | `user123` | user |


URL: http://localhost:8080/login
curl -I https://lk.ibatele.com/api/v1/balance -H "ed403b001ae9e5889f47edb21389f3eb865c72a9d0f26cab06ebfc9150a5cbe9"
