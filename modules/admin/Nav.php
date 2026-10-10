<?php

declare(strict_types=1);

namespace app\modules\admin;

/**
 * Пункты меню админ-панели.
 */
final class Nav
{
    /**
     * @return list<array{
     *     group: string,
     *     label: string,
     *     hint: string,
     *     icon: string,
     *     url: array{0: string},
     *     controller: string
     * }>
     */
    public static function items(): array
    {
        return [
            [
                'group' => 'Сайт',
                'label' => 'Настройки',
                'hint' => 'Ключи и значения сайта',
                'icon' => 'settings',
                'url' => ['/admin/settings/index'],
                'controller' => 'settings',
            ],
            [
                'group' => 'Сайт',
                'label' => 'Пользователи',
                'hint' => 'Клиенты сайта',
                'icon' => 'group',
                'url' => ['/admin/user/index'],
                'controller' => 'user',
            ],
            [
                'group' => 'Сайт',
                'label' => 'Администраторы',
                'hint' => 'Учётные записи админ-панели',
                'icon' => 'admin_panel_settings',
                'url' => ['/admin/admin-user/index'],
                'controller' => 'admin-user',
            ],
            [
                'group' => 'Сайт',
                'label' => 'Журнал входов',
                'hint' => 'История авторизаций администраторов',
                'icon' => 'history',
                'url' => ['/admin/admin-login-log/index'],
                'controller' => 'admin-login-log',
            ],
            [
                'group' => 'Сайт',
                'label' => 'Логи',
                'hint' => 'Файлы приложения и фреймворка',
                'icon' => 'article',
                'url' => ['/admin/logs/index'],
                'controller' => 'logs',
            ],
            [
                'group' => 'Avito',
                'label' => 'Токены',
                'hint' => 'OAuth-токены API',
                'icon' => 'key',
                'url' => ['/admin/avito-token/index'],
                'controller' => 'avito-token',
            ],
            [
                'group' => 'Avito',
                'label' => 'Клиенты Avito',
                'hint' => 'Собеседники мессенджера',
                'icon' => 'person',
                'url' => ['/admin/avito-user/index'],
                'controller' => 'avito-user',
            ],
            [
                'group' => 'Avito',
                'label' => 'Чаты',
                'hint' => 'Диалоги по объявлениям',
                'icon' => 'forum',
                'url' => ['/admin/avito-chat/index'],
                'controller' => 'avito-chat',
            ],
            [
                'group' => 'Avito',
                'label' => 'Сообщения',
                'hint' => 'Сообщения чатов',
                'icon' => 'mail',
                'url' => ['/admin/avito-message/index'],
                'controller' => 'avito-message',
            ],
            [
                'group' => 'Avito',
                'label' => 'Услуги',
                'hint' => 'Объявления для автозагрузки',
                'icon' => 'storefront',
                'url' => ['/admin/avito-service/index'],
                'controller' => 'avito-service',
            ],
            [
                'group' => 'Avito',
                'label' => 'Города',
                'hint' => 'Города публикации',
                'icon' => 'location_city',
                'url' => ['/admin/avito-service-city/index'],
                'controller' => 'avito-service-city',
            ],
        ];
    }
}
