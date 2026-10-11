<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\modules\ollama_chat\models\ChatMessage[] $messages */

use app\modules\ollama_chat\models\ChatMessage;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'AI-чат';
$this->params['fullWidth'] = true;

$this->registerCss(<<<'CSS'
.ollama-chat-msg{display:flex!important;flex-direction:column!important;max-width:88%;margin-bottom:1rem}
.ollama-chat-msg--user{align-self:flex-end!important;align-items:flex-end!important}
.ollama-chat-msg--assistant{align-self:flex-start!important;align-items:flex-start!important}
.ollama-chat-bubble{display:block!important;max-width:100%;box-sizing:border-box}
.ollama-chat-badge{display:inline-block!important;margin-top:4px!important;padding:2px 8px!important;border-radius:999px!important;background:#eef0f3!important;color:#9ca3af!important;font-size:10px!important;line-height:1.2!important;font-weight:400!important;white-space:nowrap!important}
CSS);

$hasHistory = $messages !== [];
$rootClass = 'ollama-chat ' . ($hasHistory ? 'ollama-chat--has-messages' : 'ollama-chat--landing');
?>
<div class="ollama-chat-page">
    <div class="<?= Html::encode($rootClass) ?>" id="ollama-chat-root">
    <div
        class="ollama-chat-hero<?= $hasHistory ? ' is-hidden' : '' ?>"
        id="ollama-chat-hero"
        aria-hidden="<?= $hasHistory ? 'true' : 'false' ?>"
    >
        <p class="ollama-chat-hero__title">
            <span class="ollama-chat-hero__icon" aria-hidden="true">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
            </span>
            Привет. Чем могу помочь?
        </p>
    </div>

    <div
        class="ollama-chat-messages-wrap<?= $hasHistory ? '' : ' is-hidden' ?>"
        id="ollama-messages-wrap"
    >
        <div
            id="ollama-chat-box"
            class="ollama-chat-box<?= $hasHistory ? '' : ' ollama-chat-box--empty' ?>"
            role="log"
            aria-live="polite"
        >
            <?php foreach ($messages as $msg): ?>
                <?php
                $isUser = $msg->role === ChatMessage::ROLE_USER;
                $roleClass = $isUser ? 'ollama-chat-msg--user' : 'ollama-chat-msg--assistant';
                $roleLabel = $isUser ? 'Вы' : 'AI';
                ?>
                <div class="ollama-chat-msg <?= $roleClass ?>" data-role="<?= Html::encode($msg->role) ?>">
                    <div class="ollama-chat-role"><?= Html::encode($roleLabel) ?></div>
                    <div class="ollama-chat-bubble"><?= Html::encode($msg->content) ?></div>
                    <span class="ollama-chat-badge"><?= Html::encode(ChatMessage::formatTime((int) $msg->created_at)) ?></span>
                </div>
            <?php endforeach ?>
        </div>
    </div>

    <div class="ollama-chat-composer-slot">
        <div class="ollama-chat-composer">
            <textarea
                id="ollama-user-input"
                class="ollama-chat-input"
                rows="1"
                placeholder="Сообщение…"
                autocomplete="off"
            ></textarea>
            <div class="ollama-chat-composer-bar">
                <div class="ollama-chat-composer-meta">
                    <button
                        type="button"
                        id="ollama-clear-btn"
                        class="ollama-chat-clear<?= $hasHistory ? '' : ' is-hidden' ?>"
                    >
                        Очистить историю
                    </button>
                    <span id="ollama-status"></span>
                </div>
                <button
                    type="button"
                    id="ollama-send-btn"
                    class="ollama-chat-send"
                    title="Отправить"
                    aria-label="Отправить"
                >
                    <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>
    </div>
</div>
<?php
$sendUrl = Url::to(['/ollamaChat/chat/send']);
$clearUrl = Url::to(['/ollamaChat/chat/clear']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;

$js = <<<JS
(function () {
    const root = document.getElementById('ollama-chat-root');
    const hero = document.getElementById('ollama-chat-hero');
    const messagesWrap = document.getElementById('ollama-messages-wrap');
    const chatBox = document.getElementById('ollama-chat-box');
    const input = document.getElementById('ollama-user-input');
    const sendBtn = document.getElementById('ollama-send-btn');
    const clearBtn = document.getElementById('ollama-clear-btn');
    const status = document.getElementById('ollama-status');

    function scrollBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function escapeHtml(text) {
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function enterConversationMode() {
        if (root.classList.contains('ollama-chat--has-messages')) {
            return;
        }
        root.classList.remove('ollama-chat--landing');
        root.classList.add('ollama-chat--has-messages');
        hero.classList.add('is-hidden');
        hero.setAttribute('aria-hidden', 'true');
        messagesWrap.classList.remove('is-hidden');
        chatBox.classList.remove('ollama-chat-box--empty');
        clearBtn.classList.remove('is-hidden');
    }

    function enterLandingMode() {
        root.classList.remove('ollama-chat--has-messages');
        root.classList.add('ollama-chat--landing');
        hero.classList.remove('is-hidden');
        hero.setAttribute('aria-hidden', 'false');
        messagesWrap.classList.add('is-hidden');
        chatBox.innerHTML = '';
        chatBox.classList.add('ollama-chat-box--empty');
        clearBtn.classList.add('is-hidden');
    }

    function formatLocalTime() {
        const d = new Date();
        const pad = (n) => String(n).padStart(2, '0');
        return pad(d.getDate()) + '.' + pad(d.getMonth() + 1) + '.' + d.getFullYear()
            + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    function addMessage(role, text, timeLabel) {
        enterConversationMode();
        const isUser = role === 'user';
        const time = timeLabel || formatLocalTime();
        const wrap = document.createElement('div');
        wrap.className = 'ollama-chat-msg ' + (isUser ? 'ollama-chat-msg--user' : 'ollama-chat-msg--assistant');
        wrap.innerHTML =
            '<div class="ollama-chat-role">' + (isUser ? 'Вы' : 'AI') + '</div>' +
            '<div class="ollama-chat-bubble">' + escapeHtml(text) + '</div>' +
            '<span class="ollama-chat-badge">' + escapeHtml(time) + '</span>';
        chatBox.appendChild(wrap);
        scrollBottom();
        return wrap;
    }

    function updateLastUserTime(timeLabel) {
        if (!timeLabel) return;
        const nodes = chatBox.querySelectorAll('.ollama-chat-msg--user .ollama-chat-badge');
        const last = nodes[nodes.length - 1];
        if (last) {
            last.textContent = timeLabel;
        }
    }

    function resizeInput() {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 192) + 'px';
    }

    async function sendMessage() {
        const text = input.value.trim();
        if (!text) return;

        addMessage('user', text);
        input.value = '';
        resizeInput();
        sendBtn.disabled = true;
        clearBtn.disabled = true;
        status.textContent = 'AI думает…';

        const body = new URLSearchParams();
        body.set('message', text);
        body.set('{$csrfParam}', '{$csrfToken}');

        try {
            const response = await fetch('{$sendUrl}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString(),
            });
            const data = await response.json();
            if (data.user_time) {
                updateLastUserTime(data.user_time);
            }
            if (data.error) {
                addMessage('assistant', '[Ошибка: ' + data.error + ']');
            } else if (data.reply) {
                addMessage('assistant', data.reply, data.assistant_time || undefined);
            }
        } catch (e) {
            addMessage('assistant', '[Сеть: ' + e.message + ']');
        } finally {
            sendBtn.disabled = false;
            clearBtn.disabled = false;
            status.textContent = '';
            input.focus();
        }
    }

    sendBtn.addEventListener('click', sendMessage);
    input.addEventListener('input', resizeInput);
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    clearBtn.addEventListener('click', async () => {
        if (!confirm('Очистить всю историю чата?')) return;
        const body = new URLSearchParams();
        body.set('{$csrfParam}', '{$csrfToken}');
        await fetch('{$clearUrl}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString(),
        });
        enterLandingMode();
        input.focus();
    });

    resizeInput();
    if (root.classList.contains('ollama-chat--has-messages')) {
        scrollBottom();
    } else {
        input.focus();
    }
})();
JS;

$this->registerJs($js);
