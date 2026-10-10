<?php

declare(strict_types=1);

namespace app\services\avito;

use app\components\avito\AvitoAuth;
use app\components\avito\AvitoClient;
use app\dto\avito\AhEmployee;
use app\dto\avito\AhUserStatus;
use Yii;
use yii\base\Component;

/**
 * Сервис взаимодействия с API «Иерархия Аккаунтов» Avito.
 */
class AvitoHierarchyService extends Component
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
     * Статус пользователя в ИА (актуальный метод).
     */
    public function checkUserStatus(string $accountId, ?int $employeeOf = null): AhUserStatus
    {
        $headers = $this->employeeHeaders($employeeOf);
        $data = $this->client->request('GET', '/checkAhUserV2', [
            'accessToken' => $this->auth->getAccessToken($accountId),
            'headers' => $headers,
        ]);

        return AhUserStatus::fromArray($data);
    }

    /**
     * Устаревший метод получения статуса пользователя в ИА.
     *
     * @deprecated используйте checkUserStatus()
     */
    public function checkUserStatusLegacy(string $accountId): AhUserStatus
    {
        $data = $this->client->request('GET', '/checkAhUserV1', [
            'accessToken' => $this->auth->getAccessToken($accountId),
        ]);

        return AhUserStatus::fromArray($data);
    }

    /**
     * Список сотрудников иерархии компании.
     *
     * @return AhEmployee[]
     */
    public function getEmployees(string $accountId, ?int $employeeOf = null): array
    {
        $data = $this->client->request('GET', '/getEmployeesV1', [
            'accessToken' => $this->auth->getAccessToken($accountId),
            'headers' => $this->employeeHeaders($employeeOf),
        ]);

        return array_map(
            static fn (array $item): AhEmployee => AhEmployee::fromArray($item),
            $data,
        );
    }

    /**
     * Полная информация о статусе пользователя в ИА.
     *
     * @return array<string, mixed>
     */
    public function getInfo(string $accountId): array
    {
        return $this->client->request('GET', '/getAhInfoV1', [
            'accessToken' => $this->auth->getAccessToken($accountId),
        ]);
    }

    /**
     * Прикрепление сотрудника к объявлениям (перелинковка).
     *
     * @param int[] $itemIds
     */
    public function linkItems(string $accountId, int $employeeId, array $itemIds): void
    {
        $this->client->post('/linkItemsV1', [
            'employeeId' => $employeeId,
            'itemIds' => array_values($itemIds),
        ], $this->auth->getAccessToken($accountId));
    }

    /**
     * Список объявлений по сотруднику с фильтром по категории.
     *
     * @return array<string, mixed>
     */
    public function listItemsByEmployeeId(
        string $accountId,
        int $employeeId,
        int $categoryId,
        ?int $lastItemId = null,
        ?int $employeeOf = null,
    ): array {
        return $this->client->request('POST', '/listItemsByEmployeeIdV1', [
            'body' => array_filter([
                'employeeId' => $employeeId,
                'categoryId' => $categoryId,
                'lastItemId' => $lastItemId,
            ], static fn ($value): bool => $value !== null),
            'accessToken' => $this->auth->getAccessToken($accountId),
            'headers' => $this->employeeHeaders($employeeOf),
        ]);
    }

    /**
     * Заголовки работы от имени сотрудника.
     *
     * @return array<string, string>
     */
    private function employeeHeaders(?int $employeeOf): array
    {
        if ($employeeOf === null) {
            return [];
        }

        return ['X-Employee-Of' => (string) $employeeOf];
    }
}

