<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\modules\admin\models\AdminLoginForm;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * Главный контроллер админ-панели: вход, выход и дашборд.
 */
class DefaultController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'logout' => ['post'],
            ],
        ];

        return $behaviors;
    }

    /**
     * Вход в админ-панель.
     *
     * @return Response|string
     */
    public function actionLogin(): Response|string
    {
        if (!Yii::$app->adminUser->isGuest) {
            return $this->redirect(['/admin']);
        }

        $model = new AdminLoginForm();

        if ($model->load($this->request->post()) && $model->login()) {
            return $this->redirect(['/admin']);
        }

        $model->password = '';

        return $this->render('login', ['model' => $model]);
    }

    /**
     * Выход из админ-панели.
     *
     * @return Response
     */
    public function actionLogout(): Response
    {
        Yii::$app->adminUser->logout();

        return $this->redirect(['/admin/login']);
    }

    /**
     * Дашборд админ-панели.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        return $this->render('index', [
            'admin' => Yii::$app->adminUser->identity,
        ]);
    }
}
