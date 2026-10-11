<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Html;

// Центральные ссылки навигации
$items = [

];

// Вход/выход выносим в отдельный список, прижатый к правому краю
$authItems = [
    [
        'label' => 'AI-чат',
        'url' => ['/chat'],
        'visible' => !Yii::$app->user->isGuest,
    ],
    [
        'label' => '<i class="fa fa-key opacity-6 me-1"></i>Вход',
        'url' => ['/login'],
        'visible' => Yii::$app->user->isGuest,
    ],
    [
        'label' => '<i class="fa fa-sign-out opacity-6 me-1"></i>Выход ('
            . Html::encode(Yii::$app->user->identity?->getFullName() ?? '') . ')',
        'url' => ['/site/logout'],
        'linkOptions' => ['data-method' => 'post'],
        'visible' => !Yii::$app->user->isGuest,
    ],
];

?>
<div class="position-fixed top-0 start-0 end-0 z-index-2 px-3 pt-3">
    <?php NavBar::begin(
        [
            'brandLabel' => Yii::$app->name,
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar navbar-expand-lg blur border-radius-xl shadow position-relative top-0 z-index-3 my-0 py-2 start-0 end-0 mx-0',
            ],
            'collapseOptions' => ['class' => 'navbar-collapse'],
        ],
    ) ?>
    <?= Nav::widget(
        [
            'options' => ['class' => 'navbar-nav mx-auto'],
            'encodeLabels' => false,
            'items' => $items,
        ],
    ) ?>
    <?= Nav::widget(
        [
            // ms-auto прижимает блок к правому краю
            'options' => ['class' => 'navbar-nav ms-auto align-items-lg-center'],
            'encodeLabels' => false,
            'items' => $authItems,
        ],
    ) ?>
    <?php NavBar::end() ?>
</div>
