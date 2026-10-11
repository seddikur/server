<?php

declare(strict_types=1);

namespace app\modules\setting;

use yii\base\Module as BaseModule;

/**
 * CRUD настроек сайта (ключ — значение в таблице settings).
 */
class Module extends BaseModule
{
    public $controllerNamespace = 'app\modules\setting\controllers';

    /** @var string */
    public $layout = '@app/modules/admin/views/layouts/main';
}
