<?php

declare(strict_types=1);

namespace app\modules\setting;

final class NavItem
{
    /**
     * @return array{
     *     group: string,
     *     label: string,
     *     hint: string,
     *     icon: string,
     *     url: array{0: string},
     *     controller: string,
     *     moduleId: string
     * }
     */
    public static function forAdmin(): array
    {
        return [
            'group' => 'Сайт',
            'label' => 'Настройки',
            'hint' => 'Ключи и значения сайта',
            'icon' => 'settings',
            'url' => ['/admin/settings/index'],
            'controller' => 'settings',
            'moduleId' => 'setting',
        ];
    }
}
