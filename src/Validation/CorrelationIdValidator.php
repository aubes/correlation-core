<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\Validation;

use Aubes\CorrelationCore\Exception\InvalidCorrelationIdException;

/**
 * Centralized validator for correlation ID values.
 *
 * Accepts visible ASCII characters only (U+0021 '!' through U+007E '~'), max 255 chars.
 *
 * This range is locale-independent and covers all visible ASCII characters
 * (excluding space) without relying on POSIX [:print:], whose behaviour
 * varies by locale. Space is excluded because correlation IDs with spaces
 * cause issues in log parsing and downstream systems.
 *
 * Control characters (including CR, LF, NUL, TAB), DEL (0x7F), and non-ASCII
 * bytes are rejected to prevent log injection and header injection attacks.
 *
 * The 255-char limit matches the practical maximum for HTTP header field values
 * and keeps correlation IDs human-readable in logs.
 */
final class CorrelationIdValidator
{
    public static function isValid(string $value): bool
    {
        return $value !== '' && \strlen($value) <= 255 && \preg_match('/^[\x21-\x7E]+$/', $value) === 1;
    }

    /**
     * Validates an HTTP header name against RFC 7230 token grammar.
     *
     * Used by HTTP-related bundles (correlation-http listener, correlation-http-client
     * decorator) to ensure the configured or programmatically-passed header name
     * cannot be used to inject CRLF sequences or invalid bytes into requests/responses.
     */
    public static function isValidHeaderName(string $name): bool
    {
        return $name !== '' && \preg_match('/^[!#$%&\'*+\-.^_`|~0-9A-Za-z]+$/', $name) === 1;
    }

    /**
     * @throws InvalidCorrelationIdException
     */
    public static function assert(string $value): string
    {
        if (!self::isValid($value)) {
            $message = \sprintf('Correlation ID must be 1-255 visible ASCII characters (\\x21-\\x7E), got %d bytes with hash prefix "%s".', \strlen($value), \substr(\hash('sha256', $value), 0, 8));

            throw new InvalidCorrelationIdException($message);
        }

        return $value;
    }
}
