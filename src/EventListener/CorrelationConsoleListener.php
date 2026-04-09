<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\EventListener;

use Aubes\CorrelationCore\Exception\InvalidCorrelationIdException;
use Aubes\CorrelationCore\Storage\CorrelationIdStorageInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputOption;

final class CorrelationConsoleListener
{
    public function __construct(
        private readonly CorrelationIdStorageInterface $storage,
    ) {
    }

    /**
     * Registers --correlation-id on every command and seeds the storage from it.
     *
     * The option is added at runtime so it does not show up in `list` / `help`
     * output, but it works on any command.
     */
    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();

        if ($command !== null && !$command->getDefinition()->hasOption('correlation-id')) {
            $command->addOption('correlation-id', null, InputOption::VALUE_REQUIRED, 'Correlation ID to use for this command');
        }

        // onlyParams: true stops the lookup at the `--` end-of-options marker.
        $correlationId = $event->getInput()->getParameterOption('--correlation-id', null, true);

        if (!\is_string($correlationId)) {
            return;
        }

        try {
            $this->storage->set($correlationId);
        } catch (InvalidCorrelationIdException $e) {
            throw new InvalidOptionException('The "--correlation-id" option must contain only printable ASCII characters (1-255 chars, no control characters).', 0, $e);
        }
    }

    public function onConsoleTerminate(ConsoleTerminateEvent $event): void
    {
        $this->storage->reset();
    }
}
