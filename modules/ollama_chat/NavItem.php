<?php

declare(strict_types=1);

namespace app\modules\ollama_chat;

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
            'label' => 'AI-чат',
            'hint' => 'Сообщения пользователей с Ollama',
            'icon' => 'forum',
            'url' => ['/admin/chat-messages'],
            'controller' => 'chat-message',
            'moduleId' => 'ollamaChatAdmin',
        ];
    }
}
