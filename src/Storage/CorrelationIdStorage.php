<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\Storage;

use Aubes\CorrelationCore\Generator\CorrelationIdGeneratorInterface;

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

        if ($correlationId === '' || \strlen($correlationId) > 255 || \preg_match('/[\x00-\x1f\x7f]/', $correlationId) === 1) {
            throw new \InvalidArgumentException(\sprintf(
                'Correlation ID must be 1-255 characters without control characters, got: "%s".',
                \addcslashes(\substr($correlationId, 0, 50), "\x00..\x1f\x7f"),
            ));
        }

        $this->correlationId = $correlationId;
    }

    public function getOrGenerate(): string
    {
        if ($this->correlationId === null) {
            $this->set($this->generator->generate());
        }

        return $this->correlationId;
    }

    public function reset(): void
    {
        $this->correlationId = null;
    }
}
