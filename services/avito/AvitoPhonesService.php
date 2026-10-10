<?php

declare(strict_types=1);

namespace app\services\avito;

use app\components\avito\AvitoAuth;
use app\components\avito\AvitoClient;
use Yii;
use yii\base\Component;

/**
 * Сервис работы с телефонами компании Avito (задел).
 */
class AvitoPhonesService extends Component
{
    public ?AvitoClient $client = null;
    public ?AvitoAuth $auth = null;

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
     * Список телефонов компании.
     *
     * @return array{phones: string[], cursor: string|null}
     */
    public function listCompanyPhones(string $accountId, ?string $cursor = null): array
    {
        $data = $this->client->request('GET', '/listCompanyPhonesV1', [
            'query' => ['cursor' => $cursor],
            'accessToken' => $this->auth->getAccessToken($accountId),
        ]);

        $result = is_array($data['result'] ?? null) ? $data['result'] : [];

        return [
            'phones' => array_map('strval', (array) ($result['phones'] ?? [])),
            'cursor' => isset($result['cursor']) ? (string) $result['cursor'] : null,
        ];
    }
}

