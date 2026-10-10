<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Вход в аккаунт';
$this->params['breadcrumbs'][] = $this->title;
$this->params['fullWidth'] = true;
$this->params['meta_description'] = 'Вход в личный кабинет по номеру телефона.';
$this->params['meta_keywords'] = 'вход, логин, телефон, авторизация';
?>
<div class="page-header d-flex flex-column min-vh-100 position-relative"
     style="background-image: url('<?= Yii::getAlias('@web/images/layouts/mountain-field-clouds-sky.jpeg') ?>');">
    <span class="mask bg-gradient-dark opacity-6"></span>

    <div class="container my-auto">
        <div class="row">
            <div class="col-lg-4 col-md-8 col-12 mx-auto">
                <div class="card z-index-0 fadeIn3 fadeInBottom border-0">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-dark shadow-dark border-radius-lg py-3 pe-1">
                            <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">Вход</h4>
                    
                        </div>
                    </div>

                    <div class="card-body">
                        <?php $form = ActiveForm::begin([
                            'id' => 'login-form',
                            'options' => ['class' => 'text-start'],
                            'fieldConfig' => [
                                'template' => "<div class=\"input-group input-group-outline\">{input}{label}</div>{error}",
                                'labelOptions' => ['class' => 'form-label'],
                                'inputOptions' => ['class' => 'form-control', 'placeholder' => ' '],
                            ],
                        ]); ?>

                        <?= $form->field($model, 'username')
                            ->textInput([
                                'autofocus' => true,
                                'autocomplete' => 'tel',
                                'inputmode' => 'tel',
                                'type' => 'tel',
                            ])
                            ->label('Телефон') ?>

                        <?= $form->field($model, 'password')
                            ->passwordInput(['autocomplete' => 'current-password'])
                            ->label('Пароль') ?>

                        <div class="form-check form-switch d-flex align-items-center mb-3">
                            <?= Html::activeCheckbox($model, 'rememberMe', [
                                'class' => 'form-check-input',
                                'id' => 'rememberMe',
                                'label' => false,
                            ]) ?>
                            <label class="form-check-label mb-0 ms-3" for="rememberMe">Запомнить меня</label>
                        </div>

                        <div class="text-center">
                            <?= Html::submitButton(
                                'Войти',
                                [
                                    'class' => 'btn bg-gradient-dark w-100 my-4 mb-2',
                                    'name' => 'login-button',
                                ],
                            ) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer position-absolute bottom-2 py-2 w-100">
        <div class="container">
            <div class="row align-items-center justify-content-lg-between">
                <div class="col-12 col-md-6 my-auto">
                    <div class="copyright text-center text-sm text-white text-lg-start">
                        &copy; <?= date('Y') ?> <?= Html::encode(Yii::$app->name) ?>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                        <li class="nav-item">
                            <a href="<?= Yii::getAlias('@web') ?>" class="nav-link text-white">Главная</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/site/contact']) ?>" class="nav-link text-white">Контакты</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
</div>
