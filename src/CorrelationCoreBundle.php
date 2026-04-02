<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore;

use Aubes\CorrelationCore\Generator\CorrelationIdGeneratorInterface;
use Aubes\CorrelationCore\Generator\UuidCorrelationIdGenerator;
use Aubes\CorrelationCore\Generator\UuidVersion;
use Aubes\CorrelationCore\Storage\CorrelationIdStorage;
use Aubes\CorrelationCore\Storage\CorrelationIdStorageInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class CorrelationCoreBundle extends AbstractBundle
{
    public function getAlias(): string
    {
        return 'correlation';
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('generator')
                    ->defaultValue(UuidCorrelationIdGenerator::class)
                    ->info('FQCN of the service implementing CorrelationIdGeneratorInterface. Defaults to UuidCorrelationIdGenerator.')
                    ->validate()
                        ->ifTrue(static fn (mixed $v): bool => !\is_string($v) || \preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff\\\\]*$/', $v) !== 1)
                        ->thenInvalid('Invalid generator %s: must be a valid PHP class name implementing CorrelationIdGeneratorInterface.')
                    ->end()
                ->end()
                ->integerNode('uuid_version')
                    ->defaultValue(7)
                    ->info('UUID version used by UuidCorrelationIdGenerator. Accepted values: 4, 6, 7. Ignored when a custom generator is configured.')
                    ->validate()
                        ->ifTrue(static fn (mixed $v): bool => !\in_array($v, [4, 6, 7], true))
                        ->thenInvalid('Invalid uuid_version %s: accepted values are 4, 6, 7.')
                    ->end()
                ->end()
            ->end()
        ;
    }

    /** @param array{generator: string, uuid_version: int} $config */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $services = $container->services();

        $services->set(CorrelationIdStorage::class)
            ->arg('$generator', service(CorrelationIdGeneratorInterface::class))
            ->tag('kernel.reset', ['method' => 'reset']);

        $services->set(UuidCorrelationIdGenerator::class)
            ->arg('$version', UuidVersion::from($config['uuid_version']));

        $builder->setAlias(CorrelationIdGeneratorInterface::class, $config['generator'])
            ->setPublic(false);

        $builder->setAlias(CorrelationIdStorageInterface::class, CorrelationIdStorage::class)
            ->setPublic(false);

        $builder->setAlias(CorrelationIdProviderInterface::class, CorrelationIdStorage::class)
            ->setPublic(false);
    }
}
