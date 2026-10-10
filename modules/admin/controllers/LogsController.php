<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\log\FileTarget;
use yii\log\Logger;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Просмотр и очистка файлов логов приложения.
 */
class LogsController extends AdminController
{
    private const LINES = [50, 100, 200, 500, 1000];

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'clear' => ['post'],
            ],
        ];

        return $behaviors;
    }

    public function actionIndex(): string
    {
        $this->view->title = 'Логи';

        $files = $this->logCatalog();
        $selected = (string) $this->request->get('log', (string) array_key_first($files));
        if (!isset($files[$selected])) {
            $selected = (string) array_key_first($files);
        }

        $lines = (int) $this->request->get('lines', 100);
        if (!in_array($lines, self::LINES, true)) {
            $lines = 100;
        }

        $light = (string) $this->request->get('light', '') === '1';

        try {
            $this->ensureLogFile($selected);
            $logData = $this->tailFile($this->logPath($selected), $lines);
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            $logData = ['Не удалось прочитать файл лога.'];
        }

        $hasLogContent = $logData !== []
            && !preg_match('/^(Файл лога|Нет доступа|Не удалось|Ошибка при чтении)/u', $logData[0]);

        return $this->render('index', [
            'logFiles' => $files,
            'selectedLog' => $selected,
            'lines' => $lines,
            'lineOptions' => self::LINES,
            'light' => $light,
            'logData' => $logData,
            'hasLogContent' => $hasLogContent,
        ]);
    }

    public function actionClear(string $log): Response
    {
        try {
            $this->ensureLogFile($log);
            $path = $this->logPath($log);
            $this->truncateLogFile($path);
            // FileTarget дописывает буфер на shutdown уже после этого экшена,
            // вместе с дампом текущего запроса. Глушим цель и обрезаем файл
            // ещё раз после финального flush логгера.
            $this->suppressFileTargets($path);
            $this->truncateAfterLoggerFlush($path);
            Yii::$app->session->setFlash('success', 'Лог успешно очищен.');
        } catch (BadRequestHttpException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Не удалось очистить лог.');
        }

        $lines = (int) $this->request->post('lines', 100);
        if (!in_array($lines, self::LINES, true)) {
            $lines = 100;
        }

        $params = [
            'index',
            'log' => isset($this->logCatalog()[$log]) ? $log : (string) array_key_first($this->logCatalog()),
            'lines' => $lines,
        ];
        if ((string) $this->request->post('light', '') === '1') {
            $params['light'] = 1;
        }

        return $this->redirect($params);
    }

    /**
     * Пункты списка — только файлы, в которые реально пишут FileTarget.
     *
     * @return array<string, array{name: string, color: string}>
     */
    private function logCatalog(): array
    {
        $catalog = [];
        foreach (Yii::$app->log->targets as $target) {
            if (!$target instanceof FileTarget || !$target->enabled || $target->logFile === null) {
                continue;
            }

            $key = $this->logKey(Yii::getAlias((string) $target->logFile));
            if (isset($catalog[$key])) {
                continue;
            }

            $levels = $this->levelNames($target);
            $catalog[$key] = [
                'name' => $levels,
                'color' => match ($levels) {
                    'error' => 'danger',
                    'warning' => 'warning',
                    default => 'primary',
                },
            ];
        }

        return $catalog !== [] ? $catalog : [
            'error' => [
                'name' => 'error',
                'color' => 'danger',
            ],
            'warning' => [
                'name' => 'warning',
                'color' => 'warning',
            ],
        ];
    }

    private function logKey(string $path): string
    {
        $name = pathinfo($path, PATHINFO_FILENAME);

        return $name !== '' ? $name : 'app';
    }

    private function levelNames(FileTarget $target): string
    {
        $mask = $target->levels;
        if ($mask === 0) {
            return 'все уровни';
        }

        $names = [];
        foreach ([
            Logger::LEVEL_ERROR => 'error',
            Logger::LEVEL_WARNING => 'warning',
            Logger::LEVEL_INFO => 'info',
            Logger::LEVEL_TRACE => 'trace',
            Logger::LEVEL_PROFILE => 'profile',
        ] as $bit => $name) {
            if (($mask & $bit) === $bit) {
                $names[] = $name;
            }
        }

        return $names === [] ? 'без уровней' : implode(', ', $names);
    }

    private function logsDir(): string
    {
        $dir = Yii::getAlias('@runtime/logs');
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException('Не удалось создать каталог логов.');
        }

        return $dir;
    }

    private function logPath(string $log): string
    {
        if (!isset($this->logCatalog()[$log])) {
            throw new BadRequestHttpException('Неизвестный тип лога.');
        }

        $path = $this->logsDir() . DIRECTORY_SEPARATOR . $log . '.log';
        $dir = realpath($this->logsDir());
        $real = realpath($path);
        if ($dir === false) {
            throw new \RuntimeException('Каталог логов недоступен.');
        }

        if ($real !== false && !str_starts_with($real, $dir . DIRECTORY_SEPARATOR)) {
            throw new BadRequestHttpException('Недопустимый путь лога.');
        }

        return $real !== false ? $real : $path;
    }

    private function ensureLogFile(string $log): void
    {
        $path = $this->logPath($log);
        if (is_file($path)) {
            return;
        }

        if (file_put_contents($path, '') === false) {
            throw new \RuntimeException('Не удалось создать файл лога.');
        }

        @chmod($path, 0664);
    }

    private function truncateLogFile(string $path): void
    {
        if (is_file($path) && !is_writable($path)) {
            throw new \RuntimeException('Нет доступа на запись файла лога.');
        }

        $fp = fopen($path, 'c+');
        if ($fp === false) {
            throw new \RuntimeException('Не удалось открыть файл лога.');
        }

        try {
            if (!flock($fp, LOCK_EX)) {
                throw new \RuntimeException('Не удалось заблокировать файл лога.');
            }
            if (!ftruncate($fp, 0)) {
                throw new \RuntimeException('Не удалось очистить файл лога.');
            }
            fflush($fp);
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }

    /**
     * FileTarget для этого файла больше не должен писать до конца запроса.
     */
    private function suppressFileTargets(string $path): void
    {
        $dispatcher = Yii::getLogger()->dispatcher;
        if (!$dispatcher instanceof \yii\log\Dispatcher) {
            return;
        }

        foreach ($dispatcher->targets as $target) {
            if (!$target instanceof \yii\log\FileTarget || $target->logFile === null) {
                continue;
            }

            $targetPath = Yii::getAlias((string) $target->logFile);
            if ($this->samePath($targetPath, $path)) {
                $target->messages = [];
                $target->enabled = false;
            }
        }
    }

    /**
     * Logger регистрирует flush на shutdown, а финальный flush — уже внутри него.
     * Обрезка должна выполниться после обоих, иначе файл снова заполнится.
     */
    private function truncateAfterLoggerFlush(string $path): void
    {
        register_shutdown_function(function () use ($path): void {
            register_shutdown_function(function () use ($path): void {
                try {
                    $this->suppressFileTargets($path);
                    $this->truncateLogFile($path);
                } catch (\Throwable $e) {
                    Yii::error($e, __METHOD__);
                }
            });
        });
    }

    private function samePath(string $left, string $right): bool
    {
        $normalize = static function (string $path): string {
            $real = realpath($path);

            return str_replace('\\', '/', $real !== false ? $real : $path);
        };

        return $normalize($left) === $normalize($right);
    }

    /**
     * @return list<string>
     */
    private function tailFile(string $filepath, int $lines): array
    {
        if (!is_file($filepath)) {
            return ['Файл лога не найден.'];
        }

        if (!is_readable($filepath)) {
            return ['Нет доступа на чтение файла лога.'];
        }

        if (filesize($filepath) === 0) {
            return ['Файл лога пуст.'];
        }

        try {
            $file = new \SplFileObject($filepath, 'r');
            $file->seek(PHP_INT_MAX);
            $totalLines = $file->key();
            $startLine = max(0, $totalLines - $lines);
            $file->seek($startLine);

            $result = [];
            while (!$file->eof()) {
                $line = $file->current();
                if (trim((string) $line) !== '') {
                    $result[] = rtrim((string) $line, "\r\n");
                }
                $file->next();
            }

            return $result === [] ? ['Файл лога не содержит данных.'] : $result;
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);

            return ['Ошибка при чтении файла лога.'];
        }
    }
}
