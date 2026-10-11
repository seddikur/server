<?php

declare(strict_types=1);

namespace app\modules\diagnostics;

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
            'group' => 'Система',
            'label' => 'Ollama',
            'hint' => 'Доступность API и тест chat',
            'icon' => 'smart_toy',
            'url' => ['/admin/diagnostics'],
            'controller' => 'diagnostics',
            'moduleId' => 'diagnostics',
        ];
    }
}
