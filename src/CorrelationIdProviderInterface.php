<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore;

/**
 * Provides the current correlation ID.
 *
 * Implementations are responsible for returning safe values (no control
 * characters, no newlines) to prevent log injection.
 *
 * Returns null when no correlation ID is available.
 */
interface CorrelationIdProviderInterface
{
    public function get(): ?string;
}
