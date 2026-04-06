<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\EventListener;

use Aubes\CorrelationCore\Storage\CorrelationIdStorageInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputOption;

final class CorrelationConsoleListener
{
    public function __construct(
        private readonly CorrelationIdStorageInterface $storage,
    ) {
    }

    /**
     * Registers the --correlation-id option and ensures a correlation ID
     * is available for the duration of the command.
     *
     * ConsoleEvents::COMMAND is dispatched before Command::run() binds
     * the input, so we can safely add the option to the definition here.
     * The value is read from raw argv via getParameterOption() because
     * input binding has not occurred yet.
     */
    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();

        if ($command !== null && !$command->getDefinition()->hasOption('correlation-id')) {
            $command->addOption('correlation-id', null, InputOption::VALUE_REQUIRED, 'Correlation ID to use for this command');
        }

        $correlationId = $event->getInput()->getParameterOption('--correlation-id', null);

        if (\is_string($correlationId)) {
            $this->storage->set($correlationId);

            return;
        }

        $this->storage->getOrGenerate();
    }

    /**
     * Resets the correlation ID after the command completes.
     *
     * This is necessary for long-running workers that execute multiple commands
     * in the same process (e.g. queue consumers). Without this reset, the
     * correlation ID from one command would leak into the next.
     *
     * Priority -100 ensures this runs after all other terminate listeners,
     * so the correlation ID remains available during cleanup handlers.
     */
    public function onConsoleTerminate(ConsoleTerminateEvent $event): void
    {
        $this->storage->reset();
    }
}
