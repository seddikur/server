<?php

declare(strict_types=1);

namespace app\modules\users;

use yii\base\Module as BaseModule;

/**
 * Пользователи сайта: identity, вход, CRUD в админке, журнал login_record.
 */
class Module extends BaseModule
{
    public $controllerNamespace = 'app\modules\users\controllers';

    /** @var string */
    public $layout = '@app/modules/admin/views/layouts/main';
}
