<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\Tests;

use Aubes\CorrelationCore\Generator\CorrelationIdGeneratorInterface;
use Aubes\CorrelationCore\Storage\CorrelationIdStorage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CorrelationIdStorage::class)]
class CorrelationIdStorageTest extends TestCase
{
    private CorrelationIdStorage $storage;

    protected function setUp(): void
    {
        $generator = $this->createStub(CorrelationIdGeneratorInterface::class);
        $generator->method('generate')->willReturn('generated-id');

        $this->storage = new CorrelationIdStorage($generator);
    }

    public function testGetReturnsNullInitially(): void
    {
        self::assertNull($this->storage->get());
    }

    public function testSetStoresCorrelationId(): void
    {
        $this->storage->set('abc-123');

        self::assertSame('abc-123', $this->storage->get());
    }

    public function testSetIsIdempotentOnceResolved(): void
    {
        $this->storage->set('first');
        $this->storage->set('second');

        self::assertSame('first', $this->storage->get());
    }

    public function testResetClearsCorrelationId(): void
    {
        $this->storage->set('abc-123');
        $this->storage->reset();

        self::assertNull($this->storage->get());
    }

    public function testCanSetAgainAfterReset(): void
    {
        $this->storage->set('first');
        $this->storage->reset();
        $this->storage->set('second');

        self::assertSame('second', $this->storage->get());
    }

    public function testGetOrGenerateGeneratesWhenEmpty(): void
    {
        $result = $this->storage->getOrGenerate();

        self::assertSame('generated-id', $result);
        self::assertSame('generated-id', $this->storage->get());
    }

    public function testGetOrGenerateReturnsExistingIdWithoutGenerating(): void
    {
        $this->storage->set('existing-id');

        $result = $this->storage->getOrGenerate();

        self::assertSame('existing-id', $result);
    }

    #[DataProvider('invalidCorrelationIdProvider')]
    public function testSetRejectsInvalidCorrelationId(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->storage->set($value);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidCorrelationIdProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'newline' => ["abc\n123"];
        yield 'carriage return' => ["abc\r123"];
        yield 'null byte' => ["abc\x00123"];
        yield 'tab' => ["abc\t123"];
        yield 'DEL character' => ["abc\x7f123"];
        yield 'exceeds 255 chars' => [str_repeat('a', 256)];
    }

    public function testSetAcceptsValidCorrelationId(): void
    {
        $this->storage->set('valid-correlation-id-123');

        self::assertSame('valid-correlation-id-123', $this->storage->get());
    }

    public function testSetAcceptsMaxLengthCorrelationId(): void
    {
        $id = str_repeat('a', 255);
        $this->storage->set($id);

        self::assertSame($id, $this->storage->get());
    }
}
