<?php

declare(strict_types=1);

namespace app\modules\setting\models\search;

use yii\data\ActiveDataProvider;

/**
 * Поиск по таблице настроек (для переноса модуля без зависимости от admin).
 */
trait SearchTrait
{
    /**
     * @param array<string, mixed> $params
     * @param list<string> $like
     * @param list<string> $exact
     */
    public function buildProvider(array $params, array $like = [], array $exact = []): ActiveDataProvider
    {
        $query = static::find();

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
