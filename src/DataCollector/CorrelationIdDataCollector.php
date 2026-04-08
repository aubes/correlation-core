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
        $this->reset();
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
        return $this->data()['correlation_id'];
    }

    public function getSource(): ?string
    {
        return $this->data()['source'];
    }

    public function getGeneratorClass(): string
    {
        return $this->data()['generator_class'];
    }

    public function getName(): string
    {
        return 'correlation_id';
    }

    public function reset(): void
    {
        $this->data = [
            'correlation_id' => null,
            'source' => null,
            'generator_class' => $this->generator::class,
        ];
    }

    /**
     * @return array{correlation_id: ?string, source: ?string, generator_class: string}
     */
    private function data(): array
    {
        /** @var array{correlation_id: ?string, source: ?string, generator_class: string} $data */
        $data = $this->data;

        return $data;
    }
}
