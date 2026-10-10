<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$avito = require __DIR__ . '/avito.php';

// Роли и права админ-панели (поле admin_users.role)
$params['adminRoles'] = require __DIR__ . '/admin-roles.php';
$params = array_merge($params, $avito['params']);

$config = [
    'id' => 'basic',
    'name' => 'СЕРВЕР',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    // Привязка интерфейса почты к конкретной реализации (symfonymailer)
    'container' => [
        'singletons' => [
            \yii\mail\MailerInterface::class => [
                'class' => \yii\symfonymailer\Mailer::class,
                // отправляем письма в файлы (переопределяется в config/local)
                'useFileTransport' => true,
                'viewPath' => '@app/mail',
            ],
        ],
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'modules' => [
        'admin' => [
            'class' => \app\modules\admin\Module::class,
            // /admin и /admin/login ведут на default-контроллер модуля
            'defaultRoute' => 'default',
        ],
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'MiOF34W1J_sAn5nugj6U6aVakorBSoSt',
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'user' => [
            'identityClass' => \app\models\User::class,
            'enableAutoLogin' => true,
        ],
        // Отдельная identity-сессия для админов, не конфликтует с фронтовым user
        'adminUser' => [
            'class' => \yii\web\User::class,
            'identityClass' => \app\models\AdminUser::class,
            'enableAutoLogin' => true,
            'loginUrl' => ['/admin/login'],
            'identityCookie' => ['name' => '_adminIdentity', 'httpOnly' => true],
            'idParam' => '__adminId',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => \yii\mail\MailerInterface::class,
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error'],
                    'logFile' => '@runtime/logs/error.log',
                ],
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['warning'],
                    'logFile' => '@runtime/logs/warning.log',
                ],
            ],
        ],
        'db' => $db,
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
            'itemFile' => 'rbac/items.php',
            'assignmentFile' => 'rbac/assignments.php',
            'ruleFile' => 'rbac/rules.php',
            'defaultRoles' => ['user', 'moder', 'admin'],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // Админ-панель: короткие URL без имени default-контроллера
                'admin' => 'admin/default/index',
                'admin/login' => 'admin/default/login',
                'admin/logout' => 'admin/default/logout',
                // Остальные маршруты модуля: /admin/<controller>/<action>
                'admin/<controller>/<action>' => 'admin/<controller>/<action>',
                'admin/<controller>' => 'admin/<controller>/index',

                // Фронтовый сайт: короткие URL без префикса /site
                'contact' => 'site/contact',
                'about' => 'site/about',
                'login' => 'site/login',
                'logout' => 'site/logout',
                'error' => 'site/error',
                // Главная
                '' => 'site/index',
            ],
        ],

    ],
    'params' => $params,
];

// Компоненты интеграции с API Avito
$config['components'] = array_merge($config['components'], $avito['components']);

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;

