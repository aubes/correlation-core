<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\Storage;

use Aubes\CorrelationCore\Exception\InvalidCorrelationIdException;
use Aubes\CorrelationCore\Generator\CorrelationIdGeneratorInterface;
use Aubes\CorrelationCore\Validation\CorrelationIdValidator;

/**
 * In-memory correlation ID storage. The only canonical implementation of
 * {@see CorrelationIdProviderInterface}: any value exposed by `get()` has
 * been validated through `set()` or comes from the configured generator.
 */
final class CorrelationIdStorage implements CorrelationIdStorageInterface
{
    private ?string $correlationId = null;

    public function __construct(
        private readonly CorrelationIdGeneratorInterface $generator,
    ) {
    }

    public function get(): string
    {
        return $this->correlationId ??= CorrelationIdValidator::assert($this->generator->generate());
    }

    /**
     * @throws InvalidCorrelationIdException
     */
    public function set(string $correlationId): void
    {
        CorrelationIdValidator::assert($correlationId);

        $this->correlationId = $correlationId;
    }

    public function reset(): void
    {
        $this->correlationId = null;
    }
}
