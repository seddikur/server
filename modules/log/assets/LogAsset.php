<?php

declare(strict_types=1);

namespace app\modules\log\assets;

use yii\bootstrap5\BootstrapAsset;
use yii\web\AssetBundle;
use yii\web\YiiAsset;

/**
 * Стили просмотра логов (переносится вместе с модулем).
 */
class LogAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/log/assets';

    /** @var list<string> */
    public $css = [
        'log.css',
    ];

    /** @var list<class-string<AssetBundle>> */
    public $depends = [
        YiiAsset::class,
        BootstrapAsset::class,
    ];
}
