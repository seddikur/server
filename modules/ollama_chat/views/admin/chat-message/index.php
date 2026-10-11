<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\modules\ollama_chat\models\search\ChatMessageSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\modules\admin\helpers\Format;
use app\modules\ollama_chat\models\ChatMessage;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\bootstrap5\LinkPager;
use yii\grid\ActionColumn;
use yii\grid\CheckboxColumn;
use yii\grid\GridView;

$roleFilter = [
    ChatMessage::ROLE_USER => 'Пользователь',
    ChatMessage::ROLE_ASSISTANT => 'AI',
    ChatMessage::ROLE_SYSTEM => 'Система',
];
?>
<div class="admin-crud">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0"><?= Html::encode($this->title) ?></h1>
        <div class="d-flex flex-wrap gap-2">
            <?= Html::beginForm(['truncate'], 'post', [
                'class' => 'd-inline',
                'onsubmit' => "return confirm('Очистить всю таблицу ollama_chat_message?');",
            ]) ?>
            <?= Html::submitButton('Очистить таблицу', ['class' => 'btn btn-outline-danger btn-sm']) ?>
            <?= Html::endForm() ?>
        </div>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'chat-message-bulk-form',
        'action' => ['bulk-delete'],
        'method' => 'post',
    ]) ?>

    <div class="mb-2">
        <?= Html::submitButton(
            'Удалить выбранные',
            [
                'class' => 'btn btn-sm btn-outline-secondary',
                'onclick' => "return confirm('Удалить выбранные сообщения?');",
            ],
        ) ?>
    </div>

    <div class="card border-0 shadow-dark border-radius-lg">
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    [
                        'class' => CheckboxColumn::class,
                        'name' => 'ids',
                        'checkboxOptions' => static fn (ChatMessage $model): array => ['value' => $model->id],
                    ],
                    'id',
                    [
                        'attribute' => 'user_id',
                        'value' => static fn (ChatMessage $model): string => $model->user !== null
                            ? Html::encode($model->user->phone)
                            : '—',
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'role',
                        'filter' => $roleFilter,
                        'value' => static fn (ChatMessage $model): string => $model->roleLabel(),
                    ],
                    [
                        'attribute' => 'content',
                        'value' => static fn (ChatMessage $model): string => Format::excerpt($model->content, 80),
                    ],
                    [
                        'attribute' => 'created_at',
                        'format' => ['datetime', 'php:d.m.Y H:i'],
                        'filter' => false,
                    ],
                    [
                        'class' => ActionColumn::class,
                        'template' => '{view} {delete}',
                        'header' => '',
                        'contentOptions' => ['class' => 'text-end text-nowrap'],
                        'buttons' => [
                            'view' => static fn ($url): string => Html::a(
                                '<i class="material-symbols-rounded">visibility</i>',
                                $url,
                                ['title' => 'Просмотр', 'class' => 'admin-crud-action'],
                            ),
                            'delete' => static fn ($url): string => Html::a(
                                '<i class="material-symbols-rounded">delete</i>',
                                $url,
                                [
                                    'title' => 'Удалить',
                                    'class' => 'admin-crud-action',
                                    'data' => [
                                        'method' => 'post',
                                        'confirm' => 'Удалить сообщение?',
                                    ],
                                ],
                            ),
                        ],
                    ],
                ],
                'emptyText' => 'Сообщений нет.',
                'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
                'options' => ['class' => 'grid-view table-responsive'],
                'pager' => ['class' => LinkPager::class],
            ]) ?>
        </div>
    </div>

    <?php ActiveForm::end() ?>
</div>
