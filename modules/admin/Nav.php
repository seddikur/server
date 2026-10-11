<?php

declare(strict_types=1);

namespace app\modules\admin;

use app\modules\log\NavItem as LogNavItem;
use app\modules\setting\NavItem as SettingNavItem;
use app\modules\users\NavItem as UsersNavItem;

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
     *     controller: string,
     *     moduleId?: string
     * }>
     */
    public static function items(): array
    {
        return [
            SettingNavItem::forAdmin(),
            UsersNavItem::forAdmin(),
            [
                'group' => 'Сайт',
                'label' => 'Администраторы',
                'hint' => 'Учётные записи админ-панели',
                'icon' => 'admin_panel_settings',
                'url' => ['/admin/admin-user/index'],
                'controller' => 'admin-user',
                'moduleId' => 'admin',
            ],
            [
                'group' => 'Сайт',
                'label' => 'Журнал входов',
                'hint' => 'История авторизаций администраторов',
                'icon' => 'history',
                'url' => ['/admin/admin-login-log/index'],
                'controller' => 'admin-login-log',
                'moduleId' => 'admin',
            ],
            LogNavItem::forAdmin(),
        ];
    }
}
