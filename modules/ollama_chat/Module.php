<?php

declare(strict_types=1);

namespace app\modules\ollama_chat;

use yii\base\Module as BaseModule;

/**
 * Чат с Ollama для авторизованных пользователей сайта (identity {{%user}}).
 */
class Module extends BaseModule
{
    public $controllerNamespace = 'app\modules\ollama_chat\controllers';

    /** @var string Layout фронта */
    public $layout = '@app/views/layouts/main';

    /** Сколько последних сообщений отправлять в контекст модели */
    public int $historyLimit = 20;

    /** Сколько сообщений показывать на странице при загрузке */
    public int $displayLimit = 50;

}
