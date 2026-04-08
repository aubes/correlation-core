<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore;

use Aubes\CorrelationCore\EventListener\CorrelationConsoleListener;
use Aubes\CorrelationCore\Generator\CorrelationIdGeneratorInterface;
use Aubes\CorrelationCore\Generator\UuidCorrelationIdGenerator;
use Aubes\CorrelationCore\Generator\UuidVersion;
use Aubes\CorrelationCore\Storage\CorrelationIdProviderInterface;
use Aubes\CorrelationCore\Storage\CorrelationIdStorage;
use Aubes\CorrelationCore\Storage\CorrelationIdStorageInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class CorrelationCoreBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('generator')
                    ->defaultValue(UuidCorrelationIdGenerator::class)
                    ->cannotBeEmpty()
                    ->info('Service ID (typically a FQCN) of a service implementing CorrelationIdGeneratorInterface. Defaults to UuidCorrelationIdGenerator.')
                ->end()
                ->integerNode('uuid_version')
                    ->defaultValue(7)
                    ->info('UUID version used by UuidCorrelationIdGenerator. Accepted values: 4, 6, 7. Ignored when a custom generator is configured.')
                    ->validate()
                        ->ifTrue(static fn (mixed $v): bool => !\in_array($v, [4, 6, 7], true))
                        ->thenInvalid('Invalid uuid_version %s: accepted values are 4, 6, 7.')
                    ->end()
                ->end()
                ->scalarNode('provider')
                    ->defaultNull()
                    ->info('Service ID (typically a FQCN) of a service implementing CorrelationIdProviderInterface. The implementation is responsible for returning only values that satisfy CorrelationIdValidator::isValid(). Defaults to the built-in CorrelationIdStorage.')
                ->end()
            ->end()
        ;
    }

    /** @param array{generator: string, uuid_version: int, provider: null|string} $config */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $services = $container->services()
            ->defaults()
                ->private()
        ;

        $services->set(CorrelationIdStorage::class)
            ->arg('$generator', service(CorrelationIdGeneratorInterface::class))
            ->tag('kernel.reset', ['method' => 'reset']);

        $services->set(UuidCorrelationIdGenerator::class)
            ->arg('$version', UuidVersion::from($config['uuid_version']));

        $services->alias(CorrelationIdGeneratorInterface::class, $config['generator']);
        $services->alias(CorrelationIdStorageInterface::class, CorrelationIdStorage::class);
        $services->alias(CorrelationIdProviderInterface::class, $config['provider'] ?? CorrelationIdStorage::class);

        $services->set(CorrelationConsoleListener::class)
            ->arg('$storage', service(CorrelationIdStorageInterface::class))
            ->tag('kernel.event_listener', ['event' => 'console.command', 'method' => 'onConsoleCommand', 'priority' => 100])
            ->tag('kernel.event_listener', ['event' => 'console.terminate', 'method' => 'onConsoleTerminate', 'priority' => -100]);
    }

    public function build(ContainerBuilder $builder): void
    {
        $builder->addCompilerPass(new Debug\TraceableStorageCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 10);
    }
}
