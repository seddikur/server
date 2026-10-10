<?php

declare(strict_types=1);

namespace app\modules\admin\helpers;

use app\models\AdminUser;
use app\models\AvitoAccount;
use app\models\AvitoService;

/**
 * Списки для выпадающих полей админки.
 */
final class Options
{
    /** @var array<int, string>|null */
    private static ?array $accounts = null;

    /** @var array<int, string>|null */
    private static ?array $services = null;

    /** @var array<int, string>|null */
    private static ?array $admins = null;

    /**
     * @return array<int, string>
     */
    public static function accounts(): array
    {
        if (self::$accounts !== null) {
            return self::$accounts;
        }

        $options = [];
        foreach (AvitoAccount::find()->orderBy(['id' => SORT_ASC])->all() as $account) {
            $options[(int) $account->id] = '#' . $account->id . ' · user ' . $account->avito_user_id;
        }

        self::$accounts = $options;

        return $options;
    }

    /**
     * @return array<int, string>
     */
    public static function services(?int $currentId = null): array
    {
        if (self::$services === null) {
            $options = [];
            $rows = AvitoService::find()->orderBy(['id' => SORT_DESC])->limit(500)->all();
            foreach ($rows as $service) {
                $options[(int) $service->id] = '#' . $service->id . ' · ' . $service->title;
            }
            self::$services = $options;
        }

        $options = self::$services;
        if ($currentId !== null && !isset($options[$currentId])) {
            $current = AvitoService::findOne($currentId);
            if ($current !== null) {
                $options[$currentId] = '#' . $current->id . ' · ' . $current->title;
            }
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    public static function admins(): array
    {
        if (self::$admins !== null) {
            return self::$admins;
        }

        $options = [];
        foreach (AdminUser::find()->orderBy(['username' => SORT_ASC])->all() as $admin) {
            $options[(int) $admin->id] = (string) $admin->username;
        }

        self::$admins = $options;

        return $options;
    }
}
