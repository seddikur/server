<?php

declare(strict_types=1);

namespace app\modules\ollama_chat\models\search;

use app\modules\ollama_chat\models\ChatMessage;
use yii\data\ActiveDataProvider;

class ChatMessageSearch extends ChatMessage
{
    use SearchTrait;

    public function rules(): array
    {
        return [
            [['id', 'user_id'], 'integer'],
            [['role', 'content'], 'safe'],
        ];
    }

    /**
     * @param array<string, mixed> $params
     */
    public function search(array $params): ActiveDataProvider
    {
        $provider = $this->buildProvider(
            $params,
            like: ['content'],
            exact: ['id', 'user_id', 'role'],
        );

        $provider->query->with(['user']);

        return $provider;
    }
}
