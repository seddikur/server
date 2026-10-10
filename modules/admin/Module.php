<?php

declare(strict_types=1);

namespace app\modules\admin;

use Yii;
use yii\base\Module as BaseModule;
/**
 * Модуль админ-панели.
 *
 * Использует отдельную identity-сессию (компонент adminUser),
 * изолированную от фронтового пользователя (компонент user).
 * Проверка доступа выполняется в базовом контроллере модуля.
 */
class Module extends BaseModule
{
    public $controllerNamespace = 'app\modules\admin\controllers';

    /** @var string $layout используемый layout админки */
    public $layout = '@app/modules/admin/views/layouts/main';

    public function init(): void
    {
        parent::init();
        Yii::$app->language = 'ru-RU';
    }
}

