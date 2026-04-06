<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\Storage;

use Aubes\CorrelationCore\Generator\CorrelationIdGeneratorInterface;
use Aubes\CorrelationCore\Validation\CorrelationIdValidator;

final class CorrelationIdStorage implements CorrelationIdStorageInterface
{
    private ?string $correlationId = null;

    public function __construct(
        private readonly CorrelationIdGeneratorInterface $generator,
    ) {
    }

    public function get(): ?string
    {
        return $this->correlationId;
    }

    /**
     * Idempotent: silently ignored if a correlation ID is already stored.
     * Call reset() first to allow setting a new value.
     */
    public function set(string $correlationId): void
    {
        if ($this->correlationId !== null) {
            return;
        }

        $this->correlationId = CorrelationIdValidator::assert($correlationId);
    }

    public function getOrGenerate(): string
    {
        if ($this->correlationId === null) {
            $this->correlationId = CorrelationIdValidator::assert($this->generator->generate());
        }

        return $this->correlationId;
    }

    public function reset(): void
    {
        $this->correlationId = null;
    }
}
