<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array<string, array{name: string, color: string}> $logFiles */
/** @var string $selectedLog */
/** @var int $lines */
/** @var list<int> $lineOptions */
/** @var bool $light */
/** @var list<string> $logData */
/** @var bool $hasLogContent */

use yii\bootstrap5\Html;
use yii\web\YiiAsset;

YiiAsset::register($this);

$selected = $logFiles[$selectedLog];
?>
<div class="admin-logs">
    <h1 class="h4 mb-3"><?= Html::encode($this->title) ?></h1>

    <div class="card border-0 shadow-dark border-radius-lg mb-3">
        <div class="card-body admin-logs-toolbar">
            <?= Html::beginForm(['index'], 'get', ['class' => 'admin-logs-filters']) ?>
                <div class="admin-logs-field">
                    <label for="log-type" class="form-label mb-1">Тип лога</label>
                    <select id="log-type" name="log" class="form-select">
                        <?php foreach ($logFiles as $type => $info): ?>
                            <option value="<?= Html::encode($type) ?>" <?= $selectedLog === $type ? 'selected' : '' ?>>
                                <?= Html::encode($info['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-logs-field">
                    <label for="lines-count" class="form-label mb-1">Количество строк</label>
                    <select id="lines-count" name="lines" class="form-select">
                        <?php foreach ($lineOptions as $linesOption): ?>
                            <option value="<?= $linesOption ?>" <?= $lines === $linesOption ? 'selected' : '' ?>>
                                <?= $linesOption ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-check admin-logs-check">
                    <input class="form-check-input" type="checkbox" id="light-mode" name="light" value="1" <?= $light ? 'checked' : '' ?>>
                    <label class="form-check-label" for="light-mode">Светлый текст</label>
                </div>

                <button type="submit" class="btn bg-gradient-dark">Показать</button>
            <?= Html::endForm() ?>

            <?php if ($hasLogContent): ?>
                <?= Html::beginForm(['clear', 'log' => $selectedLog], 'post', ['class' => 'admin-logs-clear']) ?>
                    <?= Html::hiddenInput('lines', (string) $lines) ?>
                    <?php if ($light): ?>
                        <?= Html::hiddenInput('light', '1') ?>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-outline-danger" data-confirm="Очистить этот лог?">
                        Очистить лог
                    </button>
                <?= Html::endForm() ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-dark border-radius-lg">
        <div class="card-header bg-<?= Html::encode($selected['color']) ?> d-flex justify-content-between align-items-center gap-2">
            <h2 class="h6 mb-0 <?= $selected['color'] === 'warning' ? 'text-dark' : 'text-white' ?>">
                <?= Html::encode($selected['name']) ?>
                <small class="<?= $selected['color'] === 'warning' ? 'text-body-secondary' : 'text-white-50' ?>"><?= count($logData) ?> строк</small>
            </h2>
            <?php if ($hasLogContent): ?>
                <button type="button" class="btn btn-sm btn-light" id="copy-log-btn" title="Копировать лог">
                    <i class="material-symbols-rounded align-middle">content_copy</i>
                    Копировать
                </button>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <?php if (!$hasLogContent): ?>
                <div class="alert alert-info m-3 mb-3">
                    <?= Html::encode($logData[0] ?? 'Лог пуст.') ?>
                </div>
            <?php else: ?>
                <div class="log-container<?= $light ? ' log-light' : '' ?>">
                    <div class="log-content p-3" id="log-content">
                        <?php foreach ($logData as $line): ?>
                            <div class="log-line"><?= Html::encode($line) ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<'JS'
var logContainer = document.querySelector('.admin-logs .log-container');
if (logContainer) {
    logContainer.scrollTop = logContainer.scrollHeight;
}

document.querySelectorAll('.admin-logs .log-line').forEach(function (line) {
    var text = line.textContent.toLowerCase();
    if (text.indexOf('[error]') !== -1 || text.indexOf('error') !== -1) {
        line.classList.add('log-error');
    } else if (text.indexOf('[warning]') !== -1 || text.indexOf('warning') !== -1) {
        line.classList.add('log-warning');
    } else if (text.indexOf('[info]') !== -1 || text.indexOf('info') !== -1) {
        line.classList.add('log-info');
    }
});

var copyBtn = document.getElementById('copy-log-btn');
var logContent = document.getElementById('log-content');
if (copyBtn && logContent) {
    copyBtn.addEventListener('click', function () {
        var text = Array.prototype.map.call(
            logContent.querySelectorAll('.log-line'),
            function (line) { return line.textContent; }
        ).join('\n');

        var onDone = function () {
            var original = copyBtn.innerHTML;
            copyBtn.textContent = 'Скопировано';
            copyBtn.disabled = true;
            setTimeout(function () {
                copyBtn.innerHTML = original;
                copyBtn.disabled = false;
            }, 1500);
        };

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(onDone).catch(function () {
                window.prompt('Скопируйте лог:', text);
            });
        } else {
            window.prompt('Скопируйте лог:', text);
        }
    });
}
JS);
