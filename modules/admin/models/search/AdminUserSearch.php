<?php

declare(strict_types=1);

namespace app\modules\admin\models\search;

use app\models\AdminUser;
use yii\data\ActiveDataProvider;

class AdminUserSearch extends AdminUser
{
    use AdminSearchTrait;

    public function rules(): array
    {
        return [
            [['id', 'role', 'status'], 'integer'],
            [['username', 'last_login_ip'], 'safe'],
        ];
    }

    /**
     * @param array<string, mixed> $params
     */
    public function search(array $params): ActiveDataProvider
    {
        return $this->buildProvider(
            $params,
            like: ['username', 'last_login_ip'],
            exact: ['id', 'role', 'status'],
        );
    }
}
