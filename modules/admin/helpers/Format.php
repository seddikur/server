<?php

declare(strict_types=1);

namespace app\modules\admin\helpers;

use DateTimeImmutable;
use DateTimeZone;
use Yii;

/**
 * Форматирование значений в формах и таблицах админки.
 */
final class Format
{
    public static function excerpt(?string $text, int $limit = 80): string
    {
        $text = trim((string) $text);
        if ($text === '') {
            return '—';
        }

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return mb_substr($text, 0, $limit) . '…';
    }

    public static function mask(?string $secret): string
    {
        if ($secret === null || $secret === '') {
            return '—';
        }

        $length = mb_strlen($secret);
        if ($length <= 8) {
            return str_repeat('•', $length);
        }

        return mb_substr($secret, 0, 4) . '…' . mb_substr($secret, -4);
    }

    public static function jsonText(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_array($value)) {
            return (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        if (!is_string($value)) {
            return (string) $value;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $value;
        }

        return (string) json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public static function inputDatetime(mixed $value): string
    {
        if ($value === null || $value === '' || $value === 0 || $value === '0') {
            return '';
        }

        if (is_numeric($value)) {
            $moment = (new DateTimeImmutable('@' . (int) $value))->setTimezone(self::timezone());

            return $moment->format('Y-m-d\TH:i:s');
        }

        return '';
    }

    public static function parseInputDatetime(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || (is_string($value) && ctype_digit($value))) {
            return (int) $value;
        }

        if (!is_string($value)) {
            return $value;
        }

        foreach (['!Y-m-d\TH:i:s', '!Y-m-d\TH:i'] as $pattern) {
            $moment = DateTimeImmutable::createFromFormat($pattern, $value, self::timezone());
            $errors = DateTimeImmutable::getLastErrors();
            $warningCount = is_array($errors) ? (int) ($errors['warning_count'] ?? 0) : 0;
            $errorCount = is_array($errors) ? (int) ($errors['error_count'] ?? 0) : 0;
            if ($moment !== false && $warningCount === 0 && $errorCount === 0) {
                return $moment->getTimestamp();
            }
        }

        return $value;
    }

    private static function timezone(): DateTimeZone
    {
        $name = Yii::$app->formatter->timeZone ?: 'UTC';

        return new DateTimeZone($name);
    }
}
