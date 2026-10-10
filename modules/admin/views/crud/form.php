<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var yii\db\ActiveRecord $model */
/** @var array<string, array<string, mixed>> $fields */

use app\modules\admin\helpers\Format;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

?>
<div class="admin-crud">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0"><?= Html::encode($this->title) ?></h1>
        <?= Html::a('К списку', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <div class="card border-0 shadow-dark border-radius-lg">
        <div class="card-body">
            <?php $form = ActiveForm::begin(); ?>
            <?= $form->errorSummary($model) ?>

            <div class="row">
                <?php foreach ($fields as $attribute => $config): ?>
                    <?php
                    $widget = $config['widget'] ?? 'text';
                    $wide = !empty($config['wide']) || in_array($widget, ['textarea', 'json'], true);
                    $field = $form->field($model, $attribute);
                    switch ($widget) {
                        case 'textarea':
                            $field = $field->textarea(['rows' => $config['rows'] ?? 4]);
                            break;
                        case 'password':
                            $field = $field->passwordInput(['autocomplete' => 'new-password']);
                            break;
                        case 'number':
                            $field = $field->input('number', ['step' => $config['step'] ?? 1]);
                            break;
                        case 'dropdown':
                            $options = [];
                            if (array_key_exists('prompt', $config)) {
                                $options['prompt'] = $config['prompt'];
                            }
                            $field = $field->dropDownList($config['items'] ?? [], $options);
                            break;
                        case 'checkbox':
                            $field = $field->checkbox();
                            break;
                        case 'datetime':
                            $field = $field->input('datetime-local', [
                                'value' => Format::inputDatetime($model->{$attribute}),
                                'step' => 1,
                            ]);
                            break;
                        case 'json':
                            $field = $field->textarea([
                                'rows' => $config['rows'] ?? 8,
                                'value' => Format::jsonText($model->{$attribute}),
                                'class' => 'form-control font-monospace',
                            ]);
                            break;
                        default:
                            $field = $field->textInput(['maxlength' => true]);
                    }
                    if (!empty($config['hint'])) {
                        $field->hint($config['hint']);
                    }
                    ?>
                    <div class="<?= $wide ? 'col-12' : 'col-md-6' ?>">
                        <?= $field ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="d-flex gap-2 mt-2">
                <?= Html::submitButton(
                    $model->isNewRecord ? 'Создать' : 'Сохранить',
                    ['class' => 'btn bg-gradient-dark'],
                ) ?>
                <?= Html::a(
                    'Отмена',
                    $model->isNewRecord ? ['index'] : ['view', 'id' => $model->getPrimaryKey()],
                    ['class' => 'btn btn-outline-secondary'],
                ) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
