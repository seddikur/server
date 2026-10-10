<?php

namespace app\commands;

use Yii;
use yii\console\Controller;

/**
 * Генерирование файлов разрешений RBAC для пользователей сайта.
 *
 * php yii rbac/init
 */
class RbacController extends Controller
{
    public function actionInit()
    {
        $authManager = Yii::$app->authManager;
        $authManager->removeAll();

        $guest = $authManager->createRole('guest');
        $moder = $authManager->createRole('moder');
        $user = $authManager->createRole('user');
        $admin = $authManager->createRole('admin');

        $login = $authManager->createPermission('login');
        $logout = $authManager->createPermission('logout');
        $error = $authManager->createPermission('error');
        $signUp = $authManager->createPermission('sign-up');
        $index = $authManager->createPermission('index');
        $view = $authManager->createPermission('view');
        $update = $authManager->createPermission('update');
        $delete = $authManager->createPermission('delete');

        $authManager->add($login);
        $authManager->add($logout);
        $authManager->add($error);
        $authManager->add($signUp);
        $authManager->add($index);
        $authManager->add($view);
        $authManager->add($update);
        $authManager->add($delete);

        $authManager->add($guest);
        $authManager->add($moder);
        $authManager->add($user);
        $authManager->add($admin);

        $authManager->addChild($guest, $login);
        $authManager->addChild($guest, $logout);
        $authManager->addChild($guest, $error);
        $authManager->addChild($guest, $signUp);
        $authManager->addChild($guest, $index);
        $authManager->addChild($guest, $view);

        $authManager->addChild($moder, $update);
        $authManager->addChild($moder, $guest);

        $authManager->addChild($user, $update);
        $authManager->addChild($user, $guest);

        $authManager->addChild($admin, $delete);
        $authManager->addChild($admin, $user);
        $authManager->addChild($admin, $moder);

        $this->stdout("RBAC инициализирован.\n");
    }
}
