# Модуль `diagnostics`

Проверка **Ollama** с сервера приложения (PHP + curl).

## URL

| URL | Описание |
|-----|----------|
| `/admin/diagnostics` | Страница: version, tags, модель, JSON-отчёт |
| `/admin/diagnostics/json` | JSON (config + /api/version + /api/tags) |
| `/admin/diagnostics/health` | Только ping `/api/version` (200 / 503) |
| POST `/admin/diagnostics/test-ollama` | Повтор теста, опционально chat ping |

Конфиг: `params['ollama']`, env `OLLAMA_URL`, `OLLAMA_MODEL`.
