<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\DataCollector;

use Aubes\CorrelationCore\Debug\TraceableCorrelationIdStorage;
use Aubes\CorrelationCore\Generator\CorrelationIdGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

final class CorrelationIdDataCollector extends DataCollector
{
    public function __construct(
        private readonly TraceableCorrelationIdStorage $storage,
        private readonly CorrelationIdGeneratorInterface $generator,
    ) {
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        $this->data = [
            'correlation_id' => $this->storage->get(),
            'source' => $this->storage->getSource(),
            'generator_class' => $this->generator::class,
        ];
    }

    public function getCorrelationId(): ?string
    {
        $id = $this->data['correlation_id'] ?? null;

        return \is_string($id) ? $id : null;
    }

    public function getSource(): ?string
    {
        $source = $this->data['source'] ?? null;

        return \is_string($source) ? $source : null;
    }

    public function getGeneratorClass(): string
    {
        $class = $this->data['generator_class'] ?? '';

        return \is_string($class) ? $class : '';
    }

    public function getName(): string
    {
        return 'correlation_id';
    }

    public function reset(): void
    {
        $this->data = [];
    }
}
