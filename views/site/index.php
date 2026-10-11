<?php

/** @var yii\web\View $this */

use app\assets\SiteIndexAsset;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;

$this->title = 'Авито';
$this->params['meta_description'] = ' ';
$this->params['meta_keywords'] = ' ';
$this->params['fullWidth'] = true;

SiteIndexAsset::register($this);

$backgroundUrl = Yii::getAlias('@web/images/layouts/forest.jpg');
/** @var list<string> $aphorisms */
$aphorisms = Yii::$app->params['siteIndexAphorisms'] ?? [];
if ($aphorisms === []) {
    $aphorisms = [''];
}
$aphorismIntervalMs = (int) (Yii::$app->params['siteIndexAphorismIntervalMs'] ?? 10000);
$startIndex = count($aphorisms) > 1 ? random_int(0, count($aphorisms) - 1) : 0;
$firstAphorism = $aphorisms[$startIndex];
?>
<div class="site-index">
    <div id="rainyday-scene" class="site-index-rain-scene" aria-hidden="true">
        <?= Html::img($backgroundUrl, [
            'id' => 'rainyday-background',
            'alt' => '',
        ]) ?>
    </div>
    <div class="site-index-rain-content">
        <div class="site-index-aphorism-stack" aria-live="polite">
            <h1 class="site-index-aphorism is-active"><?= Html::encode($firstAphorism) ?></h1>
            <h1 class="site-index-aphorism is-idle" aria-hidden="true"></h1>
        </div>
    </div>
</div>
<?php
$aphorismsJson = Json::encode($aphorisms);
$this->registerJs(<<<'JS'
(function () {
    function initRainyDay() {
        var image = document.getElementById('rainyday-background');
        var scene = document.getElementById('rainyday-scene');
        if (!image || !scene || typeof RainyDay === 'undefined') {
            return;
        }
        var start = function () {
            var engine = new RainyDay({
                image: image,
                parentElement: scene,
                position: 'fixed',
                top: '0px',
                left: '0px',
                width: window.innerWidth,
                height: window.innerHeight,
            });
            engine.rain([[1, 2, 8000]]);
            engine.rain([[3, 3, 0.88], [5, 5, 0.9], [6, 2, 1]], 100);
        };
        if (image.complete && image.naturalWidth) {
            start();
        } else {
            image.onload = start;
        }
    }
    window.addEventListener('load', initRainyDay);
})();
JS, View::POS_END);

if (count($aphorisms) > 1) {
    $this->registerJs(<<<JS
(function () {
    var aphorisms = {$aphorismsJson};
    var stack = document.querySelector('.site-index-aphorism-stack');
    if (!stack || aphorisms.length < 2) {
        return;
    }
    var layers = stack.querySelectorAll('.site-index-aphorism');
    if (layers.length < 2) {
        return;
    }
    var index = {$startIndex};
    var activeLayer = 0;
    var intervalMs = {$aphorismIntervalMs};

    function showNext() {
        if (stack.classList.contains('is-animating')) {
            return;
        }
        var nextLayer = 1 - activeLayer;
        var nextIndex = (index + 1) % aphorisms.length;
        var outgoing = layers[activeLayer];
        var incoming = layers[nextLayer];

        stack.classList.add('is-animating');
        incoming.textContent = aphorisms[nextIndex];
        incoming.classList.remove('is-idle');
        incoming.classList.add('is-active');
        incoming.removeAttribute('aria-hidden');

        outgoing.classList.remove('is-active');
        outgoing.classList.add('is-exiting');

        function finishOutgoing() {
            if (outgoing.classList.contains('is-idle')) {
                return;
            }
            outgoing.classList.remove('is-exiting');
            outgoing.classList.add('is-idle');
            outgoing.textContent = '';
            outgoing.setAttribute('aria-hidden', 'true');
            stack.classList.remove('is-animating');
        }

        function onFadeOut(event) {
            if (event.target !== outgoing || event.propertyName !== 'opacity') {
                return;
            }
            outgoing.removeEventListener('transitionend', onFadeOut);
            finishOutgoing();
        }
        outgoing.addEventListener('transitionend', onFadeOut);
        window.setTimeout(finishOutgoing, 1500);

        index = nextIndex;
        activeLayer = nextLayer;
    }

    window.setInterval(showNext, intervalMs);
})();
JS, View::POS_END);
}
?>
