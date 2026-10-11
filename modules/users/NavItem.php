<?php

declare(strict_types=1);

namespace app\modules\users;

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
            'label' => 'Пользователи',
            'hint' => 'Клиенты сайта',
            'icon' => 'group',
            'url' => ['/admin/user/index'],
            'controller' => 'user',
            'moduleId' => 'users',
        ];
    }
}
