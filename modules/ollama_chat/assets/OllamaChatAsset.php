<?php

declare(strict_types=1);

namespace app\modules\ollama_chat\assets;

use app\assets\AppAsset;
use yii\web\AssetBundle;

/**
 * Стили чата лежат в web/css/ollama-chat.css (статика, без publish из modules/).
 */
class OllamaChatAsset extends AssetBundle
{
    public $basePath = '@webroot';

    public $baseUrl = '@web';

    /** @var list<string> */
    public $css = [
        'css/ollama-chat.css?v=3',
    ];

    /** @var list<class-string<AssetBundle>> */
    public $depends = [
        AppAsset::class,
    ];
}
