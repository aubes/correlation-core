<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\Storage;

/**
 * Read contract exposing the current correlation ID to consumers.
 *
 * `get()` always returns a non-null, valid correlation ID and is stable
 * between two `reset()` calls on the underlying storage. The first call
 * materializes one via the configured generator if nothing was set.
 *
 * Do not implement this interface: the only canonical implementation is
 * {@see CorrelationIdStorage}, which owns the invariant through its
 * single write path. See the README for extension points.
 */
interface CorrelationIdProviderInterface
{
    public function get(): string;
}
