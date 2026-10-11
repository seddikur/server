<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $checks */
/** @var array<string, mixed>|null $ollamaTests */

use yii\bootstrap5\Html;
use yii\helpers\Json;

$ollamaTests ??= null;
$display = $ollamaTests ?? $checks;

/**
 * @param array<string, mixed> $item
 */
$statusBadge = static function (array $item): string {
    $status = (string) ($item['status'] ?? 'unknown');
    $class = match ($status) {
        'ok' => 'bg-success',
        'warning' => 'bg-warning text-dark',
        'error' => 'bg-danger',
        default => 'bg-secondary',
    };

    return Html::tag('span', strtoupper($status), ['class' => 'badge ' . $class]);
};

$version = is_array($checks['version'] ?? null) ? $checks['version'] : [];
$modelCheck = is_array($checks['model_installed'] ?? null) ? $checks['model_installed'] : [];
?>
<div class="admin-diagnostics">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 mb-0"><?= Html::encode($this->title) ?></h1>
            <p class="text-muted small mb-0">Проверка с сервера PHP (не из браузера)</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?= Html::a('JSON', ['json'], ['class' => 'btn btn-sm btn-outline-secondary', 'target' => '_blank']) ?>
            <?= Html::a('Health', ['health'], ['class' => 'btn btn-sm btn-outline-secondary', 'target' => '_blank']) ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <dl class="row mb-0 small">
                <dt class="col-sm-3">URL</dt>
                <dd class="col-sm-9"><code><?= Html::encode((string) ($config['url'] ?? '—')) ?></code></dd>
                <dt class="col-sm-3">Модель</dt>
                <dd class="col-sm-9"><code><?= Html::encode((string) ($config['model'] ?? '—')) ?></code></dd>
                <dt class="col-sm-3">Таймаут</dt>
                <dd class="col-sm-9"><?= Html::encode((string) ($config['timeout_sec'] ?? '—')) ?> с</dd>
                <dt class="col-sm-3">curl</dt>
                <dd class="col-sm-9"><?= !empty($config['curl_enabled']) ? 'да' : Html::tag('span', 'нет', ['class' => 'text-danger']) ?></dd>
            </dl>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold">/api/version</span>
                        <?= $statusBadge($version) ?>
                    </div>
                    <?php if (($version['status'] ?? '') === 'ok'): ?>
                        <p class="small mb-0">Версия: <strong><?= Html::encode((string) ($version['data']['version'] ?? '—')) ?></strong>,
                            <?= Html::encode((string) ($version['latency_ms'] ?? '')) ?> ms</p>
                    <?php else: ?>
                        <p class="small text-danger mb-0"><?= Html::encode((string) ($version['message'] ?? $version['body'] ?? 'ошибка')) ?></p>
                    <?php endif ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold">Модель в tags</span>
                        <?= $statusBadge($modelCheck) ?>
                    </div>
                    <p class="small mb-0"><?= Html::encode((string) ($modelCheck['matched'] ?? $modelCheck['message'] ?? '—')) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="fw-semibold mb-2">Модели на сервере</div>
                    <?php
                    $tags = is_array($checks['tags'] ?? null) ? $checks['tags'] : [];
                    $models = is_array($tags['data']['models'] ?? null) ? $tags['data']['models'] : [];
                    ?>
                    <?php if ($models === []): ?>
                        <p class="small text-muted mb-0">—</p>
                    <?php else: ?>
                        <ul class="small mb-0 ps-3">
                            <?php foreach (array_slice($models, 0, 8) as $m): ?>
                                <li><code><?= Html::encode((string) ($m['name'] ?? '')) ?></code></li>
                            <?php endforeach ?>
                        </ul>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2">
            <strong>Тесты</strong>
            <?= Html::beginForm(['test-ollama'], 'post', ['class' => 'd-flex flex-wrap align-items-center gap-3']) ?>
            <div class="form-check mb-0">
                <?= Html::checkbox('chat_probe', false, ['value' => '1', 'class' => 'form-check-input', 'id' => 'chat-probe']) ?>
                <label class="form-check-label small" for="chat-probe">POST /api/chat (ping)</label>
            </div>
            <?= Html::submitButton('Повторить тест', [
                'class' => 'btn btn-sm btn-primary',
                'data' => ['confirm' => 'Chat ping может занять до 2 минут. Продолжить?'],
            ]) ?>
            <?= Html::endForm() ?>
        </div>
        <div class="card-body">
            <pre class="diagnostics-pre mb-0"><?= Html::encode(Json::encode($display, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?></pre>
        </div>
    </div>
</div>
<?php
$this->registerCss(<<<'CSS'
.admin-diagnostics .diagnostics-pre {
    font-size: 0.75rem;
    line-height: 1.35;
    white-space: pre-wrap;
    word-break: break-word;
    max-height: 480px;
    overflow: auto;
    margin: 0;
    background: var(--bs-tertiary-bg);
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
}
CSS);
