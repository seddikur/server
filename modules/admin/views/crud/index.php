<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var yii\db\ActiveRecord $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array<int, mixed> $columns */

use yii\bootstrap5\Html;
use yii\bootstrap5\LinkPager;
use yii\grid\GridView;

?>
<div class="admin-crud">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0"><?= Html::encode($this->title) ?></h1>
        <?= Html::a('Создать', ['create'], ['class' => 'btn bg-gradient-dark']) ?>
    </div>

    <div class="card border-0 shadow-dark border-radius-lg">
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => $columns,
                'emptyText' => 'Записей нет.',
                'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
                'options' => ['class' => 'grid-view table-responsive'],
                'pager' => ['class' => LinkPager::class],
            ]) ?>
        </div>
    </div>
</div>
