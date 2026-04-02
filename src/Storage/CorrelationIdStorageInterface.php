<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\Storage;

use Aubes\CorrelationCore\CorrelationIdProviderInterface;
use Symfony\Contracts\Service\ResetInterface;

interface CorrelationIdStorageInterface extends CorrelationIdProviderInterface, ResetInterface
{
    public function set(string $correlationId): void;

    /**
     * Returns the current correlation ID, or generates and stores one if not yet resolved.
     */
    public function getOrGenerate(): string;
}
