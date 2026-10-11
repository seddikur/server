<?php

declare(strict_types=1);

namespace app\modules\log;

/**
 * Пункт меню для встраивания в навигацию админ-панели хост-приложения.
 */
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
            'label' => 'Логи',
            'hint' => 'Файлы приложения и фреймворка',
            'icon' => 'article',
            'url' => ['/admin/log/logs/index'],
            'controller' => 'logs',
            'moduleId' => 'log',
        ];
    }
}
