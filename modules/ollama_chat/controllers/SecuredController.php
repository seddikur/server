<?php

declare(strict_types=1);

namespace app\modules\ollama_chat\controllers;

use Yii;
use yii\web\Controller;

/**
 * Доступ только для авторизованных пользователей сайта (компонент user).
 */
abstract class SecuredController extends Controller
{
    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (Yii::$app->user->isGuest) {
            Yii::$app->user->loginRequired();

            return false;
        }

        return true;
    }
}
