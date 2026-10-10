<?php

declare(strict_types=1);

namespace app\modules\admin\assets;

use app\assets\AppAsset;
use yii\web\AssetBundle;

/**
 * Asset bundle админ-панели.
 *
 * Наследует фронтовые ассеты (Bootstrap, иконки, Material Dashboard)
 * и добавляет собственные стили админки.
 */
class AdminAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/material-symbols-rounded.css',
        'css/admin.css',
    ];
    public $js = [
        'js/admin.js',
    ];
    public $depends = [
        AppAsset::class,
    ];
}

