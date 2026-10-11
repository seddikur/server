<?php

declare(strict_types=1);

namespace app\modules\users\models\search;

use app\modules\users\models\User;
use yii\data\ActiveDataProvider;

class UserSearch extends User
{
    use SearchTrait;

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
