<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var \app\modules\admin\models\AdminUser|null $admin */

use app\modules\admin\Nav;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Дашборд';
$admin = $admin ?? Yii::$app->adminUser->identity;
?>
<div class="admin-dashboard">
    <div class="card border-0 shadow-dark border-radius-lg">
        <div class="card-body p-4">
            <h1 class="h3 fw-bold mb-3">Добро пожаловать, <?= Html::encode($admin?->username ?? '') ?></h1>
            <p class="text-body-secondary mb-0">
                Роль:
                <span class="badge bg-gradient-dark">
                    <?= Html::encode($admin instanceof \app\modules\admin\models\AdminUser ? $admin->getRoleLabel() : '—') ?>
                </span>
            </p>
        </div>
    </div>

    <div class="row mt-4 g-3">
        <?php foreach (Nav::items() as $item): ?>
            <div class="col-md-6 col-xl-4">
                <a href="<?= Url::to($item['url']) ?>" class="text-decoration-none text-reset">
                    <div class="card border-0 shadow-dark border-radius-lg h-100">
                        <div class="card-body">
                            <h5 class="fw-bold d-flex align-items-center gap-2">
                                <i class="material-symbols-rounded opacity-6"><?= Html::encode($item['icon']) ?></i>
                                <?= Html::encode($item['label']) ?>
                            </h5>
                            <p class="text-body-secondary small mb-0"><?= Html::encode($item['hint']) ?></p>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
