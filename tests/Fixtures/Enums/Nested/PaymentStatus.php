<?php

declare(strict_types=1);

namespace TypeSmith\Tests\Fixtures\Enums\Nested;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
}
