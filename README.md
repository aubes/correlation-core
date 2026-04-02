# Correlation-core

Core bundle for correlation ID management: storage, generator, and shared interfaces.

## Requirements

- PHP >= 8.2
- Symfony 6.4 / 7.4 / 8.x

## Interfaces

### `CorrelationIdProviderInterface`

Read-only access to the current correlation ID. Inject this in your services when you only need to read the ID.

```php
use Aubes\CorrelationCore\CorrelationIdProviderInterface;

class MyService
{
    public function __construct(private readonly CorrelationIdProviderInterface $provider) {}

    public function doSomething(): void
    {
        $correlationId = $this->provider->get(); // returns ?string
    }
}
```

### `CorrelationIdStorageInterface`

Full access: read, write, and reset. Extends `CorrelationIdProviderInterface` and Symfony's `ResetInterface`.

```php
public function get(): ?string;          // current ID, or null if not yet resolved
public function set(string $id): void;  // idempotent - no-op if an ID is already stored
public function getOrGenerate(): string; // returns current ID, or generates and stores one
public function reset(): void;          // clears the stored ID (called between contexts)
```

`set()` is intentionally idempotent: once a correlation ID is stored for a context, it cannot be overwritten. This prevents accidental ID changes mid-request.

### `CorrelationIdGeneratorInterface`

```php
public function generate(): string;
```

One implementation is provided:

| Class | Output | Use case |
|-------|--------|----------|
| `UuidCorrelationIdGenerator` | UUID v7 string | Default - human-readable, time-ordered |

## Custom generator

```php
use Aubes\CorrelationCore\CorrelationIdGeneratorInterface;

class MyGenerator implements CorrelationIdGeneratorInterface
{
    public function generate(): string
    {
        return bin2hex(random_bytes(16));
    }
}
```

```yaml
# config/packages/correlation.yaml
correlation:
    generator: App\MyGenerator
```

## Worker / long-running processes

`CorrelationIdStorage` implements Symfony's `ResetInterface`. The kernel calls `reset()` automatically between requests (FrankenPHP, RoadRunner, Messenger workers), ensuring the ID from one context never leaks into the next.

## License

MIT
