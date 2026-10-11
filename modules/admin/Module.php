<?php

declare(strict_types=1);

namespace app\modules\admin;

use Yii;
use yii\base\Module as BaseModule;

/**
 * Модуль админ-панели: layout, identity adminUser, CRUD, роли.
 */
class Module extends BaseModule
{
    public $controllerNamespace = 'app\modules\admin\controllers';

    /** @var string */
    public $layout = '@app/modules/admin/views/layouts/main';

    public function init(): void
    {
        parent::init();
        Yii::$app->language = 'ru-RU';

        if (!isset(Yii::$app->params['adminRoles'])) {
            Yii::$app->params['adminRoles'] = require __DIR__ . '/config/roles.php';
        }
    }

    public static function rolesConfigPath(): string
    {
        return __DIR__ . '/config/roles.php';
    }
}
