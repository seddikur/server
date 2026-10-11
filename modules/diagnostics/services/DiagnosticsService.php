<?php

declare(strict_types=1);

namespace app\modules\diagnostics\services;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Проверка доступности Ollama с сервера приложения (curl).
 */
final class DiagnosticsService
{
    /**
     * Конфиг из params / компонента ollama.
     *
     * @return array<string, mixed>
     */
    public function getOllamaConfig(): array
    {
        $params = Yii::$app->params['ollama'] ?? [];
        $timeout = (int) ($params['timeout'] ?? 300);

        return [
            'url' => $this->ollamaBaseUrl(),
            'model' => (string) ($params['model'] ?? ''),
            'timeout_sec' => $timeout,
            'temperature' => $params['temperature'] ?? null,
            'curl_enabled' => extension_loaded('curl'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getInfo(): array
    {
        return array_merge(
            ['config' => $this->getOllamaConfig()],
            $this->runOllamaTests(false),
        );
    }

    /**
     * Health-check только для Ollama (для мониторинга / curl).
     *
     * @return array<string, mixed>
     */
    public function getHealth(): array
    {
        $config = $this->getOllamaConfig();
        if (!$config['curl_enabled']) {
            return [
                'ollama' => [
                    'status' => 'error',
                    'message' => 'PHP extension curl не включён',
                ],
            ];
        }

        return [
            'ollama' => $this->mapHttpResult($this->httpGetJson($this->ollamaBaseUrl() . '/api/version')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function runOllamaTests(bool $runChatProbe = false): array
    {
        $baseUrl = $this->ollamaBaseUrl();
        $model = (string) (Yii::$app->params['ollama']['model'] ?? '');

        if (!extension_loaded('curl')) {
            return [
                'base_url' => $baseUrl,
                'model' => $model,
                'error' => 'PHP extension curl не включён',
            ];
        }

        $result = [
            'base_url' => $baseUrl,
            'model' => $model,
            'version' => $this->httpGetJson($baseUrl . '/api/version'),
            'tags' => $this->httpGetJson($baseUrl . '/api/tags'),
        ];

        $result['model_installed'] = $this->isModelInTags($result['tags'], $model);

        if ($runChatProbe && $model !== '') {
            $result['chat_probe'] = $this->ollamaChatProbe($baseUrl, $model);
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $tagsResponse
     */
    private function isModelInTags(array $tagsResponse, string $model): array
    {
        if ($model === '') {
            return ['status' => 'warning', 'message' => 'Модель не задана в config'];
        }

        if (($tagsResponse['status'] ?? '') !== 'ok') {
            return ['status' => 'error', 'message' => 'Не удалось получить /api/tags'];
        }

        $data = $tagsResponse['data'] ?? [];
        $models = is_array($data) ? ($data['models'] ?? []) : [];
        if (!is_array($models)) {
            return ['status' => 'error', 'message' => 'Неверный ответ tags'];
        }

        foreach ($models as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $name = (string) ($entry['name'] ?? '');
            if ($name === $model || str_starts_with($name, $model . ':')) {
                return ['status' => 'ok', 'matched' => $name];
            }
        }

        return ['status' => 'warning', 'message' => 'Модель не найдена в tags', 'requested' => $model];
    }

    /**
     * @return array<string, mixed>
     */
    private function ollamaChatProbe(string $baseUrl, string $model): array
    {
        $url = rtrim($baseUrl, '/') . '/api/chat';
        $payload = json_encode([
            'model' => $model,
            'messages' => [['role' => 'user', 'content' => 'ping']],
            'stream' => false,
        ], JSON_THROW_ON_ERROR);

        $start = microtime(true);
        $ch = curl_init($url);
        if ($ch === false) {
            return ['status' => 'error', 'message' => 'curl init failed'];
        }

        try {
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => min(120, (int) (Yii::$app->params['ollama']['timeout'] ?? 120)),
            ]);
            $body = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $latency = round((microtime(true) - $start) * 1000, 2);

            if ($body === false) {
                return ['status' => 'error', 'message' => curl_error($ch), 'latency_ms' => $latency];
            }

            if ($code < 200 || $code >= 300) {
                return ['status' => 'error', 'http_code' => $code, 'body' => mb_substr((string) $body, 0, 500), 'latency_ms' => $latency];
            }

            /** @var array<string, mixed> $data */
            $data = json_decode((string) $body, true) ?: [];
            $snippet = ArrayHelper::getValue($data, 'message.content', '');

            return [
                'status' => 'ok',
                'http_code' => $code,
                'latency_ms' => $latency,
                'reply_excerpt' => mb_substr((string) $snippet, 0, 200),
            ];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        } finally {
            curl_close($ch);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function httpGetJson(string $url, int $timeout = 15): array
    {
        $start = microtime(true);
        $ch = curl_init($url);
        if ($ch === false) {
            return ['status' => 'error', 'message' => 'curl init failed'];
        }

        try {
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => min(5, $timeout),
            ]);
            $body = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $latency = round((microtime(true) - $start) * 1000, 2);

            if ($body === false) {
                return [
                    'status' => 'error',
                    'message' => curl_error($ch),
                    'latency_ms' => $latency,
                ];
            }

            if ($code < 200 || $code >= 300) {
                return [
                    'status' => 'error',
                    'http_code' => $code,
                    'body' => mb_substr((string) $body, 0, 500),
                    'latency_ms' => $latency,
                ];
            }

            /** @var array<string, mixed>|null $data */
            $data = json_decode((string) $body, true);

            return [
                'status' => 'ok',
                'http_code' => $code,
                'latency_ms' => $latency,
                'data' => $data,
            ];
        } finally {
            curl_close($ch);
        }
    }

    /**
     * @param array<string, mixed> $http
     * @return array<string, mixed>
     */
    private function mapHttpResult(array $http): array
    {
        if (($http['status'] ?? '') !== 'ok') {
            return $http;
        }

        $data = $http['data'] ?? [];

        return [
            'status' => 'ok',
            'latency_ms' => $http['latency_ms'] ?? null,
            'version' => is_array($data) ? ($data['version'] ?? 'unknown') : 'unknown',
        ];
    }

    private function ollamaBaseUrl(): string
    {
        if (Yii::$app->has('ollama')) {
            $component = Yii::$app->get('ollama');
            if (property_exists($component, 'baseUrl')) {
                return rtrim((string) $component->baseUrl, '/');
            }
        }

        return rtrim((string) (Yii::$app->params['ollama']['url'] ?? 'http://localhost:11434'), '/');
    }
}
