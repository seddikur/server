<?php

declare(strict_types=1);

namespace app\modules\diagnostics;

use yii\base\Module as BaseModule;

/**
 * Диагностика приложения и проверка Ollama (админка).
 */
class Module extends BaseModule
{
    public $controllerNamespace = 'app\modules\diagnostics\controllers';

    public $layout = '@app/modules/admin/views/layouts/main';
}
