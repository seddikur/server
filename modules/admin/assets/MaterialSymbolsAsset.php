<?php

declare(strict_types=1);

namespace app\modules\admin\assets;

use yii\web\AssetBundle;

/**
 * Иконки Material Symbols Rounded для админ-панели.
 */
class MaterialSymbolsAsset extends AssetBundle
{
    /** @var list<string> */
    public $css = [
        'css/material-symbols-rounded.css',
    ];

    public $sourcePath = '@app/modules/admin/assets';
}
