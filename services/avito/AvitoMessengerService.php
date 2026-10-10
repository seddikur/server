<?php

declare(strict_types=1);

namespace app\services\avito;

use app\components\avito\AvitoAuth;
use app\components\avito\AvitoClient;
use app\exceptions\avito\AvitoException;
use app\models\AvitoChat;
use app\models\AvitoMessage;
use app\models\AvitoUser;
use Yii;
use yii\base\Component;

/**
 * Сервис работы с мессенджером Avito (приём и отправка сообщений).
 *
 * У Avito нет webhook'ов — сообщения забираются polling'ом:
 * chats → messages → markRead. Идемпотентность по (chat_id, avito_message_id).
 */
class AvitoMessengerService extends Component
{
    public ?AvitoClient $client = null;
    public ?AvitoAuth $auth = null;

    /** Scope для чтения */
    public string $readScope = 'messenger:read';

    /** Scope для записи */
    public string $writeScope = 'messenger:write';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        if ($this->client === null) {
            $this->client = Yii::$app->get('avitoClient');
        }

        if ($this->auth === null) {
            $this->auth = Yii::$app->get('avitoAuth');
        }
    }

    /**
     * Список чатов аккаунта.
     *
     * @param array{unread_only?: bool, limit?: int, offset?: int, item_ids?: int[]} $filter
     * @return array<int, array<string, mixed>>
     */
    public function getChats(string $accountId, int $userId, array $filter = []): array
    {
        $query = [
            'limit' => $filter['limit'] ?? 100,
            'offset' => $filter['offset'] ?? 0,
            'unread_only' => !empty($filter['unread_only']) ? 'true' : null,
        ];

        if (!empty($filter['item_ids'])) {
            $query['item_ids'] = implode(',', array_map('intval', $filter['item_ids']));
        }

        $data = $this->client->request('GET', $this->chatsPath($userId), [
            'query' => $query,
            'accessToken' => $this->auth->getAccessToken($accountId),
            'scopes' => $this->readScope,
        ]);

        return $this->extractList($data, 'chats');
    }

    /**
     * Сообщения чата.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMessages(string $accountId, int $userId, string $chatId, int $limit = 100, int $offset = 0): array
    {
        $data = $this->client->request('GET', $this->messagesPath($userId, $chatId), [
            'query' => ['limit' => $limit, 'offset' => $offset],
            'accessToken' => $this->auth->getAccessToken($accountId),
            'scopes' => $this->readScope,
        ]);

        return $this->extractList($data, 'messages');
    }

    /**
     * Отправка сообщения в чат.
     *
     * @return array<string, mixed>
     */
    public function sendMessage(string $accountId, int $userId, string $chatId, string $text): array
    {
        return $this->client->request('POST', $this->chatMessagesPath($userId, $chatId), [
            'body' => ['message' => ['text' => $text]],
            'accessToken' => $this->auth->getAccessToken($accountId),
            'scopes' => $this->writeScope,
        ]);
    }

    /**
     * Пометить чат прочитанным.
     */
    public function markRead(string $accountId, int $userId, string $chatId): void
    {
        $this->client->request('POST', $this->chatReadPath($userId, $chatId), [
            'accessToken' => $this->auth->getAccessToken($accountId),
            'scopes' => $this->writeScope,
        ]);
    }

    /**
     * Признак «печатает».
     */
    public function typing(string $accountId, int $userId, string $chatId): void
    {
        $this->client->request('POST', $this->chatTypingPath($userId, $chatId), [
            'accessToken' => $this->auth->getAccessToken($accountId),
            'scopes' => $this->writeScope,
        ]);
    }

    /**
     * Полный цикл polling: чаты с непрочитанными → сообщения → отметка о прочтении.
     *
     * @return array{chats: int, messages: int}
     */
    public function syncUnread(int $accountId, int $userId, int $limit = 100): array
    {
        $chats = $this->getChats((string) $accountId, $userId, ['unread_only' => true, 'limit' => $limit]);

        $chatCount = 0;
        $messageCount = 0;

        // соответствие avito_item_id → avito_service.id (для связи чата с объявлением)
        foreach ($chats as $chatRow) {
            $avitoChatId = (string) ($chatRow['id'] ?? '');
            if ($avitoChatId === '') {
                continue;
            }

            $user = AvitoUser::findOrCreate($accountId, (int) ($chatRow['user_id'] ?? 0), [
                'name' => $chatRow['user']['name'] ?? null,
                'avatar_url' => $chatRow['user']['avatar'] ?? null,
            ]);

            $chat = AvitoChat::findOrCreate($accountId, $avitoChatId, [
                'user_id' => (int) $user->id,
                'avito_item_id' => isset($chatRow['item_id']) ? (int) $chatRow['item_id'] : null,
                'unread_count' => (int) ($chatRow['unread_count'] ?? 0),
                'last_message_at' => isset($chatRow['last_message']['created'])
                    ? (int) $chatRow['last_message']['created']
                    : null,
            ]);

            $chatCount++;

            $messages = $this->getMessages((string) $accountId, $userId, $avitoChatId);

            foreach ($messages as $messageRow) {
                if (AvitoMessage::upsert((int) $chat->id, $this->normalizeMessage($messageRow, $userId))) {
                    $messageCount++;
                }
            }

            // в своей БД считаем непрочитанные сами
            $chat->unread_count = AvitoMessage::unreadCount((int) $chat->id);
            $chat->save(false);
        }

        return ['chats' => $chatCount, 'messages' => $messageCount];
    }

    /**
     * Приводит формат сообщения Avito к полям нашей модели.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function normalizeMessage(array $row, int $accountUserId): array
    {
        $authorId = isset($row['author_id']) ? (int) $row['author_id'] : null;

        return [
            'id' => $row['id'] ?? '',
            'direction' => $authorId === $accountUserId ? AvitoMessage::DIRECTION_OUT : AvitoMessage::DIRECTION_IN,
            'author_type' => isset($row['author_id']) ? AvitoMessage::AUTHOR_USER : AvitoMessage::AUTHOR_SYSTEM,
            'author_id' => $authorId,
            'body' => $row['content']['text'] ?? ($row['body'] ?? null),
            'content_type' => $row['type'] ?? 'text',
            'attachments' => $row['content']['image'] ?? ($row['attachments'] ?? null),
            'is_read' => (bool) ($row['is_read'] ?? false),
            'sent_at' => (int) ($row['created'] ?? $row['sent_at'] ?? time()),
        ];
    }

    /**
     * Достаёт список из ответа (Avito отдаёт chats/messages внутри корня либо массивом).
     *
     * @param array<string, mixed> $data
     * @return array<int, array<string, mixed>>
     */
    private function extractList(array $data, string $key): array
    {
        if (isset($data[$key]) && is_array($data[$key])) {
            return array_values(array_filter($data[$key], 'is_array'));
        }

        // на случай плоского массива
        if (array_is_list($data)) {
            return array_values(array_filter($data, 'is_array'));
        }

        return [];
    }

    private function chatsPath(int $userId): string
    {
        return '/messenger/v2/accounts/' . $userId . '/chats';
    }

    private function messagesPath(int $userId, string $chatId): string
    {
        return '/messenger/v3/accounts/' . $userId . '/chats/' . rawurlencode($chatId) . '/messages/';
    }

    private function chatMessagesPath(int $userId, string $chatId): string
    {
        return '/messenger/v1/accounts/' . $userId . '/chats/' . rawurlencode($chatId) . '/messages';
    }

    private function chatReadPath(int $userId, string $chatId): string
    {
        return '/messenger/v1/accounts/' . $userId . '/chats/' . rawurlencode($chatId) . '/read';
    }

    private function chatTypingPath(int $userId, string $chatId): string
    {
        return '/messenger/v1/accounts/' . $userId . '/chats/' . rawurlencode($chatId) . '/typing';
    }
}
