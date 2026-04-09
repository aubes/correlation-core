# Correlation-core

Core bundle for correlation ID management: storage, generator, and shared interfaces.

## Requirements

- PHP >= 8.2
- Symfony 6.4 / 7.4 / 8.x

## Contract

The core of this bundle is one invariant: **`CorrelationIdProviderInterface::get()` always returns a valid, non-null string, for the entire lifetime of a request or command**.

- `CorrelationIdStorage` is `final`, self-materializes via the configured generator on first `get()`, and memoizes the result.
- `set()` validates its input and throws `InvalidCorrelationIdException` on invalid values. Last write wins.
- `reset()` clears the stored ID between contexts (`ResetInterface`).

Downstream consumers trust the value without null checks or re-validation.

## Interfaces

### `CorrelationIdProviderInterface`

Read-only access. Inject this in your services when you only need to read the current ID.

```php
use Aubes\CorrelationCore\Storage\CorrelationIdProviderInterface;

final class MyService
{
    public function __construct(private readonly CorrelationIdProviderInterface $provider) {}

    public function doSomething(): void
    {
        $correlationId = $this->provider->get(); // guaranteed non-null, valid string
    }
}
```

> **Note**: do not implement this interface yourself. The bundle relies on `CorrelationIdStorage` being the single source of truth. If you need to seed the ID from a specific context, write a listener that calls `$storage->set(...)` (see "Extension points" below).

### `CorrelationIdStorageInterface`

Full access: read, write, and reset. Extends `CorrelationIdProviderInterface` and `ResetInterface`.

```php
public function get(): string;
public function set(string $id): void; // throws InvalidCorrelationIdException on invalid input
public function reset(): void;
```

### `CorrelationIdGeneratorInterface`

```php
public function generate(): string;
```

One implementation is provided:

| Class | Output | Use case |
|-------|--------|----------|
| `UuidCorrelationIdGenerator` | UUID v7 string | Default - human-readable, time-ordered |

## Extension points

There are exactly two ways to customize how correlation IDs are produced:

### 1. Swap the generator

Replace the default UUID v7 generator with your own implementation:

```php
use Aubes\CorrelationCore\Generator\CorrelationIdGeneratorInterface;

final class MyGenerator implements CorrelationIdGeneratorInterface
{
    public function generate(): string
    {
        return bin2hex(random_bytes(16));
    }
}
```

```yaml
# config/packages/correlation_core.yaml
correlation_core:
    generator: App\MyGenerator
```

> **Note**: the service referenced by `generator` **must** implement `Aubes\CorrelationCore\Generator\CorrelationIdGeneratorInterface`.

### 2. Seed the ID from a custom context

Inject `CorrelationIdStorageInterface` into your own listener or middleware and call `$storage->set($id)` before the first downstream read. Any `set()` wins over the generator fallback.

```php
use Aubes\CorrelationCore\Exception\InvalidCorrelationIdException;

try {
    $storage->set($idFromExternalSource);
} catch (InvalidCorrelationIdException) {
    // source untrusted: fall back to the generator by doing nothing
}
```

## Console commands

A correlation ID is generated automatically at the start of every console command and cleared when it terminates. No configuration required.

### `--correlation-id` option

Any command can receive a specific correlation ID via the global `--correlation-id` option:

```bash
php bin/console app:process-orders --correlation-id=01JQWXYZ...
```

If provided, the value is validated (printable ASCII, max 255 chars) before being stored. If omitted, a UUID v7 is generated.

## License

MIT
