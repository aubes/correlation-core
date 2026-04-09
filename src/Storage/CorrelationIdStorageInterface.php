<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\Storage;

use Aubes\CorrelationCore\Exception\InvalidCorrelationIdException;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Write contract for the correlation ID storage.
 *
 * Used by boundary code (HTTP listener, console listener, Messenger
 * middleware, custom bootstrap listeners) that needs to push a value
 * into the storage. Consumers that only read should typehint
 * {@see CorrelationIdProviderInterface} instead.
 */
interface CorrelationIdStorageInterface extends CorrelationIdProviderInterface, ResetInterface
{
    /**
     * Stores a correlation ID. Overwrites any previously stored value.
     *
     * @throws InvalidCorrelationIdException when the value does not satisfy
     *                                       {@see \Aubes\CorrelationCore\Validation\CorrelationIdValidator::isValid()}
     */
    public function set(string $correlationId): void;
}
