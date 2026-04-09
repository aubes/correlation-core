<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\Debug;

use Aubes\CorrelationCore\Storage\CorrelationIdStorageInterface;

final class TraceableCorrelationIdStorage implements CorrelationIdStorageInterface
{
    private ?string $source = null;

    public function __construct(
        private readonly CorrelationIdStorageInterface $decorated,
    ) {
    }

    public function get(): string
    {
        $result = $this->decorated->get();

        // If no one has set or read a value before this call, the decorated
        // storage just materialized one via the generator. Record that fact.
        $this->source ??= 'generated';

        return $result;
    }

    public function set(string $correlationId): void
    {
        $this->decorated->set($correlationId);
        $this->source = 'provided';
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function reset(): void
    {
        $this->decorated->reset();
        $this->source = null;
    }
}
