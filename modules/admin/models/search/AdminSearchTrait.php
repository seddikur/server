<?php

declare(strict_types=1);

namespace app\modules\admin\models\search;

use yii\data\ActiveDataProvider;

/**
 * Общий поиск для таблиц админки.
 *
 * Класс, который подключает трейт, должен быть ActiveRecord.
 */
trait AdminSearchTrait
{
    /**
     * @param array<string, mixed> $params
     * @param list<string> $like
     * @param list<string> $exact
     * @param list<string> $with
     */
    public function buildProvider(
        array $params,
        array $like = [],
        array $exact = [],
        array $with = [],
    ): ActiveDataProvider {
        $query = static::find();
        if ($with !== []) {
            $query->with($with);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            $query->where('0=1');

            return $dataProvider;
        }

        foreach ($exact as $attribute) {
            $query->andFilterWhere([$attribute => $this->{$attribute}]);
        }

        foreach ($like as $attribute) {
            $query->andFilterWhere(['like', $attribute, $this->{$attribute}]);
        }

        return $dataProvider;
    }
}
