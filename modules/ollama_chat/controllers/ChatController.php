<?php

declare(strict_types=1);

namespace app\modules\ollama_chat\controllers;

use app\modules\ollama_chat\assets\OllamaChatAsset;
use app\modules\ollama_chat\components\OllamaClient;
use app\modules\ollama_chat\models\ChatMessage;
use app\modules\ollama_chat\Module;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Response;

class ChatController extends SecuredController
{
    private const MAX_MESSAGE_LENGTH = 32000;

    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'send' => ['post'],
                    'clear' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        OllamaChatAsset::register($this->view);
        $this->view->title = 'AI-чат';

        /** @var Module $module */
        $module = $this->module;
        $userId = (int) Yii::$app->user->id;

        $messages = ChatMessage::find()
            ->where(['user_id' => $userId])
            ->orderBy(['id' => SORT_ASC])
            ->limit($module->displayLimit)
            ->all();

        return $this->render('index', [
            'messages' => $messages,
        ]);
    }

    public function actionSend(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $userText = trim((string) Yii::$app->request->post('message', ''));
        if ($userText === '') {
            return ['error' => 'Пустое сообщение.'];
        }
        if (mb_strlen($userText) > self::MAX_MESSAGE_LENGTH) {
            return ['error' => 'Слишком длинное сообщение.'];
        }

        $userId = (int) Yii::$app->user->id;

        $userMsg = new ChatMessage([
            'user_id' => $userId,
            'role' => ChatMessage::ROLE_USER,
            'content' => $userText,
        ]);
        if (!$userMsg->save()) {
            return ['error' => 'Не удалось сохранить сообщение.'];
        }

        /** @var Module $module */
        $module = $this->module;

        $history = ChatMessage::find()
            ->where(['user_id' => $userId])
            ->orderBy(['id' => SORT_ASC])
            ->limit($module->historyLimit)
            ->all();

        $messages = [];
        foreach ($history as $row) {
            $messages[] = [
                'role' => $row->role,
                'content' => $row->content,
            ];
        }

        /** @var OllamaClient $ollama */
        $ollama = Yii::$app->get('ollama');

        try {
            $reply = $ollama->chat($messages);
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), 'ollama');

            return ['error' => 'Модель недоступна: ' . $e->getMessage()];
        }

        $assistantMsg = new ChatMessage([
            'user_id' => $userId,
            'role' => ChatMessage::ROLE_ASSISTANT,
            'content' => $reply,
        ]);
        if (!$assistantMsg->save()) {
            return [
                'error' => 'Ответ получен, но не сохранён.',
                'reply' => $reply,
                'user_time' => ChatMessage::formatTime((int) $userMsg->created_at),
            ];
        }

        return [
            'reply' => $reply,
            'message_id' => $assistantMsg->id,
            'user_time' => ChatMessage::formatTime((int) $userMsg->created_at),
            'assistant_time' => ChatMessage::formatTime((int) $assistantMsg->created_at),
        ];
    }

    public function actionClear(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        ChatMessage::deleteAll(['user_id' => (int) Yii::$app->user->id]);

        return ['ok' => true];
    }
}
