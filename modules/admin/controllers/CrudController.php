<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\modules\admin\helpers\Format;
use Yii;
use yii\db\ActiveRecord;
use yii\filters\VerbFilter;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Общие страницы списка, просмотра, создания и редактирования.
 */
abstract class CrudController extends AdminController
{
    /**
     * @return class-string<ActiveRecord>
     */
    abstract protected function modelClass(): string;

    /**
     * @return class-string
     */
    abstract protected function searchModelClass(): string;

    abstract protected function entityTitle(): string;

    /**
     * @return array<int, mixed>
     */
    abstract protected function gridColumns(): array;

    /**
     * @return array<int, mixed>
     */
    abstract protected function detailAttributes(ActiveRecord $model): array;

    /**
     * @return array<string, array<string, mixed>>
     */
    abstract protected function formFields(ActiveRecord $model): array;

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'delete' => ['post'],
            ],
        ];

        return $behaviors;
    }

    public function actionIndex(): string
    {
        $this->view->title = $this->entityTitle();
        $class = $this->searchModelClass();
        $searchModel = new $class();
        if (!method_exists($searchModel, 'search')) {
            throw new \LogicException('Модель поиска не реализует search().');
        }

        return $this->render('@app/modules/admin/views/crud/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search($this->request->queryParams),
            'columns' => array_merge($this->gridColumns(), [$this->actionColumn()]),
        ]);
    }

    public function actionView(int $id): string
    {
        $model = $this->findModel($id);
        $this->view->title = $this->recordLabel($model);

        return $this->render('@app/modules/admin/views/crud/view', [
            'model' => $model,
            'attributes' => $this->detailAttributes($model),
        ]);
    }

    public function actionCreate(): Response|string
    {
        $this->view->title = 'Новая запись · ' . $this->entityTitle();

        return $this->saveForm($this->newModel());
    }

    public function actionUpdate(int $id): Response|string
    {
        $model = $this->findModel($id);
        $this->view->title = 'Редактирование · ' . $this->recordLabel($model);

        return $this->saveForm($model);
    }

    public function actionDelete(int $id): Response
    {
        $model = $this->findModel($id);
        $reason = $this->deleteBlockedReason($model);
        if ($reason !== null) {
            Yii::$app->session->setFlash('error', $reason);

            return $this->redirect(['index']);
        }

        try {
            $model->delete();
            Yii::$app->session->setFlash('success', 'Запись удалена.');
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Не удалось удалить запись. Возможно, на неё ссылаются другие данные.');
        }

        return $this->redirect(['index']);
    }

    protected function newModel(): ActiveRecord
    {
        $class = $this->modelClass();

        return new $class();
    }

    protected function recordLabel(ActiveRecord $model): string
    {
        return $this->entityTitle() . ' #' . $model->getPrimaryKey();
    }

    protected function deleteBlockedReason(ActiveRecord $model): ?string
    {
        return null;
    }

    protected function beforePersist(ActiveRecord $model): void
    {
    }

    /**
     * @return array<string, mixed>
     */
    protected function timeColumn(string $attribute): array
    {
        return [
            'attribute' => $attribute,
            'format' => ['datetime', 'php:d.m.Y H:i'],
            'filter' => false,
        ];
    }

    protected function prepare(ActiveRecord $model): void
    {
        $this->attachCasting($model);
    }

    protected function findModel(int $id): ActiveRecord
    {
        $class = $this->modelClass();
        $model = $class::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Запись не найдена.');
        }

        return $model;
    }

    /**
     * @return array<string, mixed>
     */
    private function actionColumn(): array
    {
        return [
            'class' => ActionColumn::class,
            'template' => '{view} {update} {delete}',
            'header' => '',
            'contentOptions' => ['class' => 'text-end text-nowrap'],
            'buttons' => [
                'view' => static fn ($url, ...$extra): string => Html::a(
                    '<i class="material-symbols-rounded">visibility</i>',
                    $url,
                    ['title' => 'Просмотр', 'aria-label' => 'Просмотр', 'class' => 'admin-crud-action'],
                ),
                'update' => static fn ($url, ...$extra): string => Html::a(
                    '<i class="material-symbols-rounded">edit</i>',
                    $url,
                    ['title' => 'Изменить', 'aria-label' => 'Изменить', 'class' => 'admin-crud-action'],
                ),
                'delete' => static fn ($url, ...$extra): string => Html::a(
                    '<i class="material-symbols-rounded">delete</i>',
                    $url,
                    [
                        'title' => 'Удалить',
                        'aria-label' => 'Удалить',
                        'class' => 'admin-crud-action text-danger',
                        'data-method' => 'post',
                        'data-confirm' => 'Удалить эту запись?',
                    ],
                ),
            ],
        ];
    }

    private function saveForm(ActiveRecord $model): Response|string
    {
        $this->prepare($model);

        if ($model->load($this->request->post())) {
            try {
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Сохранено.');

                    return $this->redirect(['view', 'id' => $model->getPrimaryKey()]);
                }
            } catch (\Throwable $e) {
                Yii::error($e, __METHOD__);
                $model->addError('id', 'Не удалось сохранить запись.');
            }
        }

        return $this->render('@app/modules/admin/views/crud/form', [
            'model' => $model,
            'fields' => $this->formFields($model),
        ]);
    }

    private function attachCasting(ActiveRecord $model): void
    {
        $datetime = [];
        $json = [];
        foreach ($this->formFields($model) as $attribute => $config) {
            $widget = $config['widget'] ?? 'text';
            if ($widget === 'datetime') {
                $datetime[] = $attribute;
            }
            if ($widget === 'json') {
                $json[] = $attribute;
            }
        }

        $model->on(ActiveRecord::EVENT_BEFORE_VALIDATE, static function ($event) use ($model, $datetime, $json): void {
            foreach ($datetime as $attribute) {
                $model->{$attribute} = Format::parseInputDatetime($model->{$attribute});
            }

            foreach ($model->attributes() as $attribute) {
                if ($model->{$attribute} === '' && !in_array($attribute, $json, true)) {
                    $model->{$attribute} = null;
                }
            }

            foreach ($json as $attribute) {
                $value = $model->{$attribute};
                if ($value === null || $value === '') {
                    $model->{$attribute} = null;
                    continue;
                }
                if (!is_string($value)) {
                    continue;
                }
                json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $model->addError($attribute, 'Некорректный JSON.');
                }
            }
        });

        $normalize = function ($event) use ($model, $json): void {
            foreach ($json as $attribute) {
                $value = $model->{$attribute};
                if ($value === null || $value === '') {
                    $model->{$attribute} = null;
                    continue;
                }
                if (is_array($value)) {
                    $model->{$attribute} = json_encode($value, JSON_UNESCAPED_UNICODE);
                    continue;
                }
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $model->{$attribute} = json_encode($decoded, JSON_UNESCAPED_UNICODE);
                    }
                }
            }
            $this->beforePersist($model);
        };

        $model->on(ActiveRecord::EVENT_BEFORE_INSERT, $normalize);
        $model->on(ActiveRecord::EVENT_BEFORE_UPDATE, $normalize);
    }
}
