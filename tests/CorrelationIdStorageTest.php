<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\Tests;

use Aubes\CorrelationCore\Exception\InvalidCorrelationIdException;
use Aubes\CorrelationCore\Generator\CorrelationIdGeneratorInterface;
use Aubes\CorrelationCore\Storage\CorrelationIdStorage;
use Aubes\CorrelationCore\Validation\CorrelationIdValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CorrelationIdStorage::class)]
#[CoversClass(CorrelationIdValidator::class)]
#[CoversClass(InvalidCorrelationIdException::class)]
class CorrelationIdStorageTest extends TestCase
{
    public function testGetMaterializesFromGeneratorOnFirstCall(): void
    {
        $generator = $this->createMock(CorrelationIdGeneratorInterface::class);
        $generator->expects(self::once())->method('generate')->willReturn('generated-id');

        $storage = new CorrelationIdStorage($generator);

        self::assertSame('generated-id', $storage->get());
    }

    public function testGetIsMemoizedAfterFirstCall(): void
    {
        $generator = $this->createMock(CorrelationIdGeneratorInterface::class);
        $generator->expects(self::once())->method('generate')->willReturn('generated-id');

        $storage = new CorrelationIdStorage($generator);

        self::assertSame('generated-id', $storage->get());
        self::assertSame('generated-id', $storage->get());
        self::assertSame('generated-id', $storage->get());
    }

    public function testSetStoresCorrelationId(): void
    {
        $storage = $this->createStorage();

        $storage->set('abc-123');

        self::assertSame('abc-123', $storage->get());
    }

    public function testSetBeforeGetSkipsGenerator(): void
    {
        $generator = $this->createMock(CorrelationIdGeneratorInterface::class);
        $generator->expects(self::never())->method('generate');

        $storage = new CorrelationIdStorage($generator);
        $storage->set('explicit-id');

        self::assertSame('explicit-id', $storage->get());
    }

    public function testSetOverwritesExistingValue(): void
    {
        $storage = $this->createStorage();

        $storage->set('first');
        $storage->set('second');

        self::assertSame('second', $storage->get());
    }

    public function testSetOverwritesGeneratedValue(): void
    {
        $storage = $this->createStorage('generated-id');

        self::assertSame('generated-id', $storage->get());

        $storage->set('contextual-id');

        self::assertSame('contextual-id', $storage->get());
    }

    public function testResetClearsCorrelationId(): void
    {
        $storage = $this->createStorage('fresh-id');

        $storage->set('abc-123');
        $storage->reset();

        // After reset, the next get() re-materializes from the generator.
        self::assertSame('fresh-id', $storage->get());
    }

    public function testCanSetAgainAfterReset(): void
    {
        $storage = $this->createStorage();

        $storage->set('first');
        $storage->reset();
        $storage->set('second');

        self::assertSame('second', $storage->get());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidCorrelationIdProvider')]
    public function testSetRejectsInvalidCorrelationId(string $value): void
    {
        $storage = $this->createStorage();

        $this->expectException(InvalidCorrelationIdException::class);
        $storage->set($value);
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
        yield 'space' => ['abc 123'];
        yield 'DEL character' => ["abc\x7f123"];
        yield 'high ASCII' => ["abc\x80"];
        yield 'exceeds 255 chars' => [\str_repeat('a', 256)];
    }

    public function testSetAcceptsValidCorrelationId(): void
    {
        $storage = $this->createStorage();

        $storage->set('valid-correlation-id-123');

        self::assertSame('valid-correlation-id-123', $storage->get());
    }

    public function testSetAcceptsMaxLengthCorrelationId(): void
    {
        $storage = $this->createStorage();
        $id = \str_repeat('a', 255);

        $storage->set($id);

        self::assertSame($id, $storage->get());
    }

    public function testGetValidatesGeneratedValueAndThrowsOnBrokenGenerator(): void
    {
        $generator = $this->createStub(CorrelationIdGeneratorInterface::class);
        $generator->method('generate')->willReturn("broken\nvalue");

        $storage = new CorrelationIdStorage($generator);

        $this->expectException(InvalidCorrelationIdException::class);
        $storage->get();
    }

    private function createStorage(string $generatedId = 'generated-id'): CorrelationIdStorage
    {
        $generator = $this->createStub(CorrelationIdGeneratorInterface::class);
        $generator->method('generate')->willReturn($generatedId);

        return new CorrelationIdStorage($generator);
    }
}
