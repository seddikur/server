<?php

declare(strict_types=1);

namespace app\modules\admin\models\search;

use app\models\User;
use yii\data\ActiveDataProvider;

class UserSearch extends User
{
    use AdminSearchTrait;

    public function rules(): array
    {
        return [
            [['id', 'status'], 'integer'],
            [['phone', 'email', 'first_name', 'last_name'], 'safe'],
        ];
    }

    /**
     * @param array<string, mixed> $params
     */
    public function search(array $params): ActiveDataProvider
    {
        return $this->buildProvider(
            $params,
            like: ['phone', 'email', 'first_name', 'last_name'],
            exact: ['id', 'status'],
        );
    }
}
