<?php

/**
 * Роли и права админ-панели (поле admin_users.role).
 *
 * Ключи permissions: {controller-id}.{action-id} внутри модуля admin и вложенных модулей.
 */
return [
    'roles' => [
        100 => [
            'code' => 'admin',
            'label' => 'Админ',
            'description' => 'Полный доступ к админ-панели',
        ],
        50 => [
            'code' => 'manager',
            'label' => 'Менеджер',
            'description' => 'Ограниченный доступ',
        ],
    ],

    'permissions' => [
        'default.index',
        'admin-user.index',
        'admin-user.view',
        'admin-user.create',
        'admin-user.update',
        'admin-user.delete',
        'admin-login-log.index',
        'admin-login-log.view',
        'admin-login-log.create',
        'admin-login-log.update',
        'admin-login-log.delete',
        'user.index',
        'user.view',
        'user.create',
        'user.update',
        'user.delete',
        'logs.index',
        'logs.clear',
        'settings.index',
        'settings.view',
        'settings.create',
        'settings.update',
        'settings.delete',
        'chat-message.index',
        'chat-message.view',
        'chat-message.delete',
        'chat-message.bulk-delete',
        'chat-message.truncate',
    ],

    'role_permissions' => [
        100 => ['*'],
        50 => [
            'default.index',
            'user.index',
            'user.view',
            'user.create',
            'user.update',
            'admin-login-log.index',
            'admin-login-log.view',
        ],
    ],
];
