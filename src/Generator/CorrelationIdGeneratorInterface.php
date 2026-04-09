<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\Generator;

interface CorrelationIdGeneratorInterface
{
    /**
     * Generate a new correlation ID: 1 to 255 visible ASCII characters
     * (\x21-\x7E). Invalid values are rejected by
     * {@see \Aubes\CorrelationCore\Storage\CorrelationIdStorage::set()}.
     */
    public function generate(): string;
}
