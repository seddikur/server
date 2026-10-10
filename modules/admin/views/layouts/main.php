<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var string $content */

use app\modules\admin\assets\AdminAsset;
use app\widgets\Alert;
use yii\helpers\Html;

AdminAsset::register($this);
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag([
    'name' => 'viewport',
    'content' => 'width=device-width, initial-scale=1',
]);

$isGuest = Yii::$app->adminUser->isGuest;
$identity = Yii::$app->adminUser->identity;

// Страницы на всю ширину (например, логин) рендерятся без сайдбара и обёртки .container
$isFullWidth = !empty($this->params['fullWidth']);
$hasSidenav = !$isGuest && !$isFullWidth;

$bodyClass = $hasSidenav
    ? 'bg-body-tertiary admin-layout has-sidenav'
    : 'd-flex flex-column h-100 bg-body-tertiary admin-layout';
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100" data-bs-theme="light">

<head>
    <?php $this->head() ?>
    <title><?= Html::encode($this->title) ?> &middot; Админ-панель</title>
</head>

<body class="<?= $bodyClass ?>">
    <?php $this->beginBody() ?>

    <?php if ($hasSidenav): ?>
        <div class="admin-sidenav-backdrop" id="sidenavBackdrop" hidden></div>
        <?= $this->render('_sidebar') ?>
    <?php endif; ?>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <?php if (!$isGuest): ?>
            <header class="admin-topbar">
                <div class="admin-topbar-bar">
                    <?php if ($hasSidenav): ?>
                        <button type="button" class="admin-menu-toggle" id="iconNavbarSidenav" aria-label="Открыть меню">
                            <i class="material-symbols-rounded">menu</i>
                        </button>
                    <?php endif; ?>
                    <span class="admin-topbar-user">
                        <i class="material-symbols-rounded">account_circle</i>
                        <span class="admin-topbar-username"><?= Html::encode($identity?->username ?? '') ?></span>
                    </span>
                </div>
            </header>

            <?php if (!$isFullWidth): ?>
                <nav class="admin-breadcrumbs" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?= Yii::getAlias('@web/admin') ?>">Админ-панель</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?= Html::encode($this->title) ?>
                        </li>
                    </ol>
                </nav>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($isFullWidth): ?>
            <?= $content ?>
        <?php else: ?>
            <div class="container-fluid py-4">
                <?= Alert::widget() ?>
                <?= $content ?>
            </div>
        <?php endif; ?>
    </main>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>