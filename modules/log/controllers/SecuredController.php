<?php

declare(strict_types=1);

namespace app\modules\log\controllers;

use app\modules\log\Module;
use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\User;

/**
 * Базовый контроллер модуля log: авторизация через настраиваемый user-компонент.
 */
abstract class SecuredController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        /** @var Module $module */
        $module = $this->module;

        /** @var User $user */
        $user = Yii::$app->get($module->userComponent);

        if ($user->isGuest) {
            $user->loginRequired();

            return false;
        }

        $identity = $user->identity;
        if ($identity === null) {
            $user->loginRequired();

            return false;
        }

        if ($module->accessCheck !== null && !($module->accessCheck)($identity)) {
            throw new ForbiddenHttpException('Доступ запрещён.');
        }

        if (method_exists($identity, 'canAccessAdmin') && !$identity->canAccessAdmin()) {
            throw new ForbiddenHttpException('Доступ запрещён.');
        }

        return true;
    }
}
