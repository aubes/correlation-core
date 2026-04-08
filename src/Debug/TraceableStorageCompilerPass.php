<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\Debug;

use Aubes\CorrelationCore\DataCollector\CorrelationIdDataCollector;
use Aubes\CorrelationCore\Generator\CorrelationIdGeneratorInterface;
use Aubes\CorrelationCore\Storage\CorrelationIdStorageInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class TraceableStorageCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('profiler')) {
            return;
        }

        if ($container->hasParameter('kernel.debug') && $container->getParameter('kernel.debug') !== true) {
            return;
        }

        $container->register(TraceableCorrelationIdStorage::class, TraceableCorrelationIdStorage::class)
            ->setDecoratedService(CorrelationIdStorageInterface::class)
            ->setArgument('$decorated', new Reference('.inner'))
            ->addTag('kernel.reset', ['method' => 'reset']);

        $container->register(CorrelationIdDataCollector::class, CorrelationIdDataCollector::class)
            ->setArgument('$storage', new Reference(TraceableCorrelationIdStorage::class))
            ->setArgument('$generator', new Reference(CorrelationIdGeneratorInterface::class))
            ->addTag('data_collector', [
                'template' => '@CorrelationCore/Collector/correlation_id.html.twig',
                'id' => 'correlation_id',
            ]);
    }
}
