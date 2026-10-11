<?php

declare(strict_types=1);

namespace app\modules\admin\helpers;

use app\modules\admin\models\AdminUser;

/**
 * Списки для выпадающих полей админки.
 */
final class Options
{
    /** @var array<int, string>|null */
    private static ?array $admins = null;

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
