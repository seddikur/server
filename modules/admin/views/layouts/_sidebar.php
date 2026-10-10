<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\helpers\Html;

use app\modules\admin\Nav;

$controller = Yii::$app->controller;
$currentController = $controller?->id ?? '';

$navLinkClass = static function (string $ctrl) use ($currentController): string {
    $isActive = $currentController === $ctrl;

    return 'nav-link ' . ($isActive ? 'active bg-gradient-dark text-white' : 'text-dark');
};

$items = Nav::items();
?>
<aside
    class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 bg-white"
    id="sidenav-main"
>
    <div class="sidenav-header">
        <button type="button" class="admin-sidenav-close" id="iconSidenav" aria-label="Закрыть меню">
            <i class="material-symbols-rounded">close</i>
        </button>
        <a class="navbar-brand px-4 py-3 m-0" href="<?= Yii::getAlias('@web/admin') ?>">
            <span class="ms-1 text-sm text-dark fw-bold"><?= Html::encode(Yii::$app->name) ?></span>
        </a>
    </div>

    <hr class="horizontal dark mt-0 mb-2">

    <div class="w-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link <?= $currentController === 'default' ? 'active bg-gradient-dark text-white' : 'text-dark' ?>"
                   href="<?= Yii::getAlias('@web/admin') ?>">
                    <i class="material-symbols-rounded opacity-5">dashboard</i>
                    <span class="nav-link-text ms-1">Дашборд</span>
                </a>
            </li>

            <?php $group = null; ?>
            <?php foreach ($items as $item): ?>
                <?php if ($item['group'] !== $group): ?>
                    <?php $group = $item['group']; ?>
                    <li class="nav-item">
                        <div class="nav-section"><?= Html::encode($group) ?></div>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="<?= $navLinkClass($item['controller']) ?>" href="<?= \yii\helpers\Url::to($item['url']) ?>">
                        <i class="material-symbols-rounded opacity-5"><?= Html::encode($item['icon']) ?></i>
                        <span class="nav-link-text ms-1"><?= Html::encode($item['label']) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="sidenav-footer position-absolute w-100 bottom-0">
        <div class="mx-3">
            <?= Html::beginForm(['/admin/logout'], 'post', ['class' => 'w-100']) ?>
            <?= Html::submitButton(
                '<i class="material-symbols-rounded opacity-5 me-1">logout</i>Выход',
                ['class' => 'btn bg-gradient-dark w-100 mb-3'],
            ) ?>
            <?= Html::endForm() ?>
        </div>
    </div>
</aside>

