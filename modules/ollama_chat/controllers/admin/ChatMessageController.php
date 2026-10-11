<?php

declare(strict_types=1);

namespace app\modules\ollama_chat\controllers\admin;

use app\modules\admin\controllers\CrudController;
use app\modules\ollama_chat\models\ChatMessage;
use app\modules\ollama_chat\models\search\ChatMessageSearch;
use Yii;
use yii\db\ActiveRecord;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class ChatMessageController extends CrudController
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs']['actions']['bulk-delete'] = ['post'];
        $behaviors['verbs']['actions']['truncate'] = ['post'];

        return $behaviors;
    }

    public function actionIndex(): string
    {
        $this->view->title = $this->entityTitle();
        $searchModel = new ChatMessageSearch();

        return $this->render('@app/modules/ollama_chat/views/admin/chat-message/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search($this->request->queryParams),
        ]);
    }

    public function actionCreate(): Response
    {
        throw new ForbiddenHttpException('Создание сообщений вручную недоступно.');
    }

    public function actionUpdate(int $id): Response
    {
        throw new ForbiddenHttpException('Редактирование сообщений недоступно.');
    }

    public function actionBulkDelete(): Response
    {
        $ids = $this->request->post('ids', []);
        if (!is_array($ids)) {
            $ids = [];
        }

        $ids = array_values(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0));
        if ($ids === []) {
            Yii::$app->session->setFlash('warning', 'Не выбрано ни одной записи.');

            return $this->redirect(['index']);
        }

        $deleted = ChatMessage::deleteAll(['id' => $ids]);
        Yii::$app->session->setFlash('success', 'Удалено записей: ' . $deleted . '.');

        return $this->redirect(['index']);
    }

    public function actionTruncate(): Response
    {
        $deleted = ChatMessage::deleteAll();

        Yii::$app->session->setFlash('success', 'Таблица очищена. Удалено записей: ' . $deleted . '.');

        return $this->redirect(['index']);
    }

    protected function modelClass(): string
    {
        return ChatMessage::class;
    }

    protected function searchModelClass(): string
    {
        return ChatMessageSearch::class;
    }

    protected function entityTitle(): string
    {
        return 'Сообщения AI-чата';
    }

    protected function gridColumns(): array
    {
        return [];
    }

    protected function detailAttributes(ActiveRecord $model): array
    {
        if (!$model instanceof ChatMessage) {
            return [];
        }

        return [
            'id',
            [
                'attribute' => 'user_id',
                'value' => static fn (ChatMessage $m): string => $m->user !== null
                    ? $m->user->phone . ' (' . $m->user->getFullName() . ')'
                    : '—',
            ],
            [
                'attribute' => 'role',
                'value' => static fn (ChatMessage $m): string => $m->roleLabel(),
            ],
            'content:ntext',
            $this->timeColumn('created_at'),
        ];
    }

    protected function formFields(ActiveRecord $model): array
    {
        return [];
    }

    protected function recordLabel(ActiveRecord $model): string
    {
        if (!$model instanceof ChatMessage) {
            return parent::recordLabel($model);
        }

        return '#' . $model->id . ' · ' . $model->roleLabel();
    }
}
