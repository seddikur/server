<?php

declare(strict_types=1);

namespace app\modules\admin\models\search;

use app\modules\admin\models\AdminLoginLog;
use yii\data\ActiveDataProvider;

class AdminLoginLogSearch extends AdminLoginLog
{
    use AdminSearchTrait;

    public function rules(): array
    {
        return [
            [['id', 'admin_user_id'], 'integer'],
            [['username', 'ip', 'user_agent'], 'safe'],
        ];
    }

    /**
     * @param array<string, mixed> $params
     */
    public function search(array $params): ActiveDataProvider
    {
        return $this->buildProvider(
            $params,
            like: ['username', 'ip', 'user_agent'],
            exact: ['id', 'admin_user_id'],
            with: ['adminUser'],
        );
    }
}
