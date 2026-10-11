<?php

declare(strict_types=1);

namespace app\modules\diagnostics\controllers;

use app\modules\admin\controllers\AdminController;
use app\modules\diagnostics\services\DiagnosticsService;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Response;

class DiagnosticsController extends AdminController
{
    private DiagnosticsService $service;

    public function __construct($id, $module, ?DiagnosticsService $service = null, $config = [])
    {
        $this->service = $service ?? new DiagnosticsService();
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'test-ollama' => ['post'],
            ],
        ];

        return $behaviors;
    }

    public function actionIndex(): string
    {
        $this->view->title = 'Ollama';

        return $this->render('index', [
            'config' => $this->service->getOllamaConfig(),
            'checks' => $this->service->runOllamaTests(false),
            'ollamaTests' => null,
        ]);
    }

    public function actionJson(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $this->service->getInfo();
    }

    public function actionHealth(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $checks = $this->service->getHealth();
        $ollama = $checks['ollama'] ?? [];
        $ok = is_array($ollama) && ($ollama['status'] ?? '') === 'ok';
        Yii::$app->response->statusCode = $ok ? 200 : 503;

        return $checks;
    }

    public function actionTestOllama(): string
    {
        $runChat = (string) Yii::$app->request->post('chat_probe', '0') === '1';

        $this->view->title = 'Ollama';

        $checks = $this->service->runOllamaTests($runChat);

        return $this->render('index', [
            'config' => $this->service->getOllamaConfig(),
            'checks' => $checks,
            'ollamaTests' => null,
        ]);
    }
}
