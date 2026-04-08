<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\Storage;

/**
 * Provides the current correlation ID.
 *
 * *Contract:** `get()` must return either `null` (no correlation ID available)
 * or a string that satisfies {@see \Aubes\CorrelationCore\Validation\CorrelationIdValidator::isValid()}
 *
 * Returns null when no correlation ID is available.
 */
interface CorrelationIdProviderInterface
{
    public function get(): ?string;
}
