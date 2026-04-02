<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\Tests\Generator;

use Aubes\CorrelationCore\Generator\UuidCorrelationIdGenerator;
use Aubes\CorrelationCore\Generator\UuidVersion;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UuidCorrelationIdGenerator::class)]
class UuidCorrelationIdGeneratorTest extends TestCase
{
    private const UUID_V4_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    private const UUID_V6_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-6[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    private const UUID_V7_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    public function testDefaultVersionIsV7(): void
    {
        $generator = new UuidCorrelationIdGenerator();

        self::assertMatchesRegularExpression(self::UUID_V7_PATTERN, $generator->generate());
    }

    public function testGenerateV4(): void
    {
        $generator = new UuidCorrelationIdGenerator(UuidVersion::V4);

        self::assertMatchesRegularExpression(self::UUID_V4_PATTERN, $generator->generate());
    }

    public function testGenerateV6(): void
    {
        $generator = new UuidCorrelationIdGenerator(UuidVersion::V6);

        self::assertMatchesRegularExpression(self::UUID_V6_PATTERN, $generator->generate());
    }

    public function testGenerateV7(): void
    {
        $generator = new UuidCorrelationIdGenerator(UuidVersion::V7);

        self::assertMatchesRegularExpression(self::UUID_V7_PATTERN, $generator->generate());
    }

    public function testGenerateReturnsUniqueValues(): void
    {
        $generator = new UuidCorrelationIdGenerator();

        self::assertNotSame($generator->generate(), $generator->generate());
    }
}
