# Модуль `ollama_chat`

Чат с [Ollama](https://ollama.com/) для **авторизованных пользователей сайта** (`user`, компонент `Yii::$app->user`). Страница на фронте: `/chat`.

## Структура

```
modules/ollama_chat/
├── Module.php
├── README.md
├── components/OllamaClient.php
├── controllers/ChatController.php, SecuredController.php
├── models/ChatMessage.php
├── migrations/
├── assets/OllamaChatAsset.php
└── views/chat/index.php

Стили: `web/css/ollama-chat.css` (статический файл, не publish из modules).
```

## Подключение

1. Миграция: `php yii migrate` (таблица `ollama_chat_message`).

2. В `config/web.php`:

```php
'modules' => [
    'ollamaChat' => ['class' => \app\modules\ollama_chat\Module::class],
],
'components' => [
    'ollama' => [
        'class' => \app\modules\ollama_chat\components\OllamaClient::class,
        'baseUrl' => $params['ollama']['url'],
        'model' => $params['ollama']['model'],
        'timeout' => (int) $params['ollama']['timeout'],
    ],
],
// urlManager:
'chat' => 'ollamaChat/chat/index',
'chat/<action:\w+>' => 'ollamaChat/chat/<action>',
```

3. Параметры в `config/params.php` (локально переопределите URL и модель).

4. После входа на `/login` пользователь перенаправляется на `/chat`.

Требуется PHP **ext-curl** и доступ с сервера приложения до HTTP API Ollama.
