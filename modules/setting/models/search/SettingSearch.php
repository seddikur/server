<?php

declare(strict_types=1);

namespace app\modules\setting\models\search;

use app\modules\setting\models\Setting;
use yii\data\ActiveDataProvider;

class SettingSearch extends Setting
{
    use SearchTrait;

    public function rules(): array
    {
        return [
            [['id'], 'integer'],
            [['key', 'value', 'description'], 'safe'],
        ];
    }

    /**
     * @param array<string, mixed> $params
     */
    public function search(array $params): ActiveDataProvider
    {
        return $this->buildProvider(
            $params,
            like: ['key', 'value', 'description'],
            exact: ['id'],
        );
    }
}
