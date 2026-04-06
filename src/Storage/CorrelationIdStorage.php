<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\Storage;

use Aubes\CorrelationCore\Exception\InvalidCorrelationIdException;
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

        $this->correlationId = self::validate($correlationId);
    }

    public function getOrGenerate(): string
    {
        if ($this->correlationId === null) {
            $this->correlationId = self::validate($this->generator->generate());
        }

        return $this->correlationId;
    }

    private static function validate(string $correlationId): string
    {
        if ($correlationId === '' || \strlen($correlationId) > 255 || \preg_match('/[\x00-\x1f\x7f]/', $correlationId) === 1) {
            throw new InvalidCorrelationIdException(\sprintf('Correlation ID must be 1-255 characters without control characters, got: "%s".', \addcslashes(\substr($correlationId, 0, 50), "\x00..\x1f\x7f")));
        }

        return $correlationId;
    }

    public function reset(): void
    {
        $this->correlationId = null;
    }
}
