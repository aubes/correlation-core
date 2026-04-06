<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\Storage;

/**
 * Provides the current correlation ID.
 *
 * Implementations are responsible for returning safe values (visible ASCII only,
 * see Aubes\CorrelationCore\Validation\CorrelationIdValidator) to prevent log
 * injection.
 *
 * Returns null when no correlation ID is available.
 */
interface CorrelationIdProviderInterface
{
    public function get(): ?string;
}
