<?php

declare(strict_types=1);

namespace Aubes\CorrelationCore\Generator;

/**
 * UUID versions supported by UuidCorrelationIdGenerator.
 *
 * - V4: random UUID (RFC 4122 §4.4) - no temporal ordering, maximum entropy.
 * - V6: reordered time UUID (RFC 9562 §5.6) - time-sortable, good DB locality.
 * - V7: Unix-epoch-based UUID (RFC 9562 §5.7) - time-sortable, monotonic, recommended default.
 */
enum UuidVersion: int
{
    case V4 = 4;
    case V6 = 6;
    case V7 = 7;
}
