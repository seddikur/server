<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var yii\db\ActiveRecord $model */
/** @var array<int, mixed> $attributes */

use yii\bootstrap5\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

YiiAsset::register($this);
?>
<div class="admin-crud">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0"><?= Html::encode($this->title) ?></h1>
        <div class="d-flex gap-2">
            <?= Html::a('К списку', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?= Html::a('Изменить', ['update', 'id' => $model->getPrimaryKey()], ['class' => 'btn bg-gradient-dark']) ?>
            <?= Html::a('Удалить', ['delete', 'id' => $model->getPrimaryKey()], [
                'class' => 'btn btn-outline-danger',
                'data-method' => 'post',
                'data-confirm' => 'Удалить эту запись?',
            ]) ?>
        </div>
    </div>

    <div class="card border-0 shadow-dark border-radius-lg">
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => $attributes,
                'options' => ['class' => 'table table-striped detail-view admin-detail mb-0'],
            ]) ?>
        </div>
    </div>
</div>
