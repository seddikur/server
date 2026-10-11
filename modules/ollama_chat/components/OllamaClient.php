<?php

declare(strict_types=1);

namespace app\modules\ollama_chat\components;

use yii\base\Component;

/**
 * HTTP-клиент Ollama (/api/chat), без Python — только curl.
 */
class OllamaClient extends Component
{
    public string $baseUrl = 'http://localhost:11434';

    public string $model = 'qwen3-coder-30b-ctx';

    public int $timeout = 300;

    public float $temperature = 0.7;

    /**
     * @param list<array{role: string, content: string}> $messages
     */
    public function chat(array $messages): string
    {
        $url = rtrim($this->baseUrl, '/') . '/api/chat';
        $payload = json_encode([
            'model' => $this->model,
            'messages' => $messages,
            'stream' => false,
            'options' => [
                'temperature' => $this->temperature,
            ],
        ], JSON_THROW_ON_ERROR);

        $ch = curl_init($url);
        if ($ch === false) {
            throw new \RuntimeException('Не удалось инициализировать curl.');
        }

        try {
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeout,
            ]);

            $body = curl_exec($ch);
            if ($body === false) {
                throw new \RuntimeException('Ollama: ' . curl_error($ch));
            }

            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($status < 200 || $status >= 300) {
                throw new \RuntimeException('Ollama HTTP ' . $status . ': ' . $body);
            }

            /** @var array<string, mixed> $data */
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

            if (isset($data['error']) && is_string($data['error'])) {
                throw new \RuntimeException('Ollama: ' . $data['error']);
            }

            $content = $data['message']['content'] ?? null;

            return is_string($content) ? $content : '';
        } finally {
            curl_close($ch);
        }
    }
}
