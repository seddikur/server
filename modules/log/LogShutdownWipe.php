<?php

declare(strict_types=1);

namespace app\modules\log;

use yii\base\BootstrapInterface;

/**
 * Регистрирует shutdown раньше Logger, чтобы после финального flush обрезать очищенные лог-файлы.
 */
final class LogShutdownWipe implements BootstrapInterface
{
    /** @var list<string> */
    private static array $paths = [];

    private static bool $registered = false;

    public function bootstrap($app): void
    {
        if (self::$registered) {
            return;
        }

        self::$registered = true;
        register_shutdown_function(static function (): void {
            foreach (self::$paths as $path) {
                if (!is_file($path)) {
                    continue;
                }

                $fp = @fopen($path, 'c+');
                if ($fp === false) {
                    continue;
                }

                if (flock($fp, LOCK_EX)) {
                    ftruncate($fp, 0);
                    fflush($fp);
                    flock($fp, LOCK_UN);
                }

                fclose($fp);
            }

            self::$paths = [];
        });
    }

    public static function defer(string $path): void
    {
        if (!in_array($path, self::$paths, true)) {
            self::$paths[] = $path;
        }
    }
}
