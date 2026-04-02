<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\Generator;

interface CorrelationIdGeneratorInterface
{
    public function generate(): string;
}
