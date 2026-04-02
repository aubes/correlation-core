<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\Generator;

use Symfony\Component\Uid\Uuid;

final readonly class UuidCorrelationIdGenerator implements CorrelationIdGeneratorInterface
{
    public function __construct(
        private UuidVersion $version = UuidVersion::V7,
    ) {
    }

    public function generate(): string
    {
        return (string) match ($this->version) {
            UuidVersion::V4 => Uuid::v4(),
            UuidVersion::V6 => Uuid::v6(),
            UuidVersion::V7 => Uuid::v7(),
        };
    }
}
