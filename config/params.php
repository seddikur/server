<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',

    'ollama' => [
      //  'url' => getenv('OLLAMA_URL') ?: 'http://91.236.252.80:11434',
        'url' => getenv('OLLAMA_URL') ?: 'http://localhost:11434',
        'model' => getenv('OLLAMA_MODEL') ?: 'qwen3-coder-30b-ctx',
        'timeout' => (int) (getenv('OLLAMA_TIMEOUT') ?: 300),
        'temperature' => 0.7,
    ],

    /** @see config/site_index_aphorisms.php */
    'siteIndexAphorisms' => require __DIR__ . '/site_index_aphorisms.php',
    'siteIndexAphorismIntervalMs' => 10000,
];
