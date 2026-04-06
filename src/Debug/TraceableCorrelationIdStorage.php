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

    public function get(): ?string
    {
        return $this->decorated->get();
    }

    public function set(string $correlationId): void
    {
        $alreadySet = $this->decorated->get() !== null;

        $this->decorated->set($correlationId);

        if (!$alreadySet && $this->decorated->get() !== null) {
            $this->source = 'provided';
        }
    }

    public function getOrGenerate(): string
    {
        $alreadySet = $this->decorated->get() !== null;

        $result = $this->decorated->getOrGenerate();

        if (!$alreadySet) {
            $this->source = 'generated';
        }

        return $result;
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
