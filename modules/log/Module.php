<?php

declare(strict_types=1);

namespace app\modules\log;

use yii\base\Module as BaseModule;

/**
 * Просмотр и очистка файлов логов (FileTarget из конфигурации log).
 *
 * Подключается как вложенный модуль админки или отдельно — см. README.md.
 */
class Module extends BaseModule
{
    public $controllerNamespace = 'app\modules\log\controllers';

    /** Layout страниц (обычно layout админ-панели хост-приложения). */
    public $layout = '@app/modules/admin/views/layouts/main';

    /** Компонент Yii для проверки авторизации (например adminUser). */
    public $userComponent = 'adminUser';

    /**
     * Дополнительная проверка доступа: callable(identity): bool или null (только авторизация).
     *
     * @var callable|null
     */
    public $accessCheck;

}
