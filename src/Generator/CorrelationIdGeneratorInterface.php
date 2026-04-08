<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\Generator;

interface CorrelationIdGeneratorInterface
{
    /**
     * Generate a new correlation ID.
     *
     * Implementations MUST return a value that satisfies
     * {@see \Aubes\CorrelationCore\Validation\CorrelationIdValidator::isValid()}:
     * 1 to 255 visible ASCII characters (\x21-\x7E). Values that fail this
     * contract will be rejected downstream - the core storage throws
     * {@see \Aubes\CorrelationCore\Exception\InvalidCorrelationIdException}.
     *
     * Implementations MUST NOT return an empty string.
     */
    public function generate(): string;
}
