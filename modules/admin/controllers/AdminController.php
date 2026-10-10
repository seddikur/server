<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

/**
 * Базовый контроллер админ-панели.
 *
 * Проверяет доступ через отдельный компонент adminUser (identity AdminUser).
 * Экшен login доступен без авторизации.
 */
abstract class AdminController extends Controller
{
    /**
     * Экшены, доступные без авторизации.
     *
     * @return array<int, string>
     */
    protected function openActions(): array
    {
        return ['login'];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (in_array($action->id, $this->openActions(), true)) {
            return true;
        }

        $identity = Yii::$app->adminUser->identity;

        if (Yii::$app->adminUser->isGuest || $identity === null) {
            Yii::$app->adminUser->loginRequired();
            return false;
        }

        if (!$identity->canAccessAdmin()) {
            throw new ForbiddenHttpException('Доступ к админ-панели запрещён.');
        }

        return true;
    }
}
