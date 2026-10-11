<?php

declare(strict_types=1);

namespace app\modules\admin\assets;

use app\assets\AppAsset;
use yii\web\AssetBundle;

/**
 * Стили и скрипты админ-панели (каталог modules/admin/assets).
 */
class AdminAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/admin/assets';

    /** @var list<string> */
    public $css = [
        'css/admin.css',
    ];

    /** @var list<string> */
    public $js = [
        'js/admin.js',
    ];

    /** @var list<class-string<AssetBundle>> */
    public $depends = [
        AppAsset::class,
        MaterialSymbolsAsset::class,
    ];
}
