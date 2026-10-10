<?php

$env = getenv('YII_ENV') ?: 'prod';

$file = __DIR__ . "/db-{$env}.php";

if (!is_file($file)) {
    throw new \yii\base\InvalidConfigException("DB config not found: {$file}");
}

return require $file;
