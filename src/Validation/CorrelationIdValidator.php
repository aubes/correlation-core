<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\Validation;

use Aubes\CorrelationCore\Exception\InvalidCorrelationIdException;

/**
 * Accepts visible ASCII only (U+0021 '!' through U+007E '~'), max 255 chars.
 * Space and control bytes (CR, LF, NUL, TAB, DEL) are rejected to prevent log
 * and header injection. The 255-char limit matches HTTP header field practice.
 *
 * @internal Shared infrastructure between the correlation-* bundles. End-user
 *           code must not call these helpers: validation is enforced by
 *           {@see \Aubes\CorrelationCore\Storage\CorrelationIdStorage::set()}.
 */
final class CorrelationIdValidator
{
    public static function isValid(string $value): bool
    {
        return $value !== '' && \strlen($value) <= 255 && \preg_match('/^[\x21-\x7E]+$/', $value) === 1;
    }

    /**
     * Validates an HTTP header name against RFC 7230 token grammar.
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
