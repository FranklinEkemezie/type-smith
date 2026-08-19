<?php

declare(strict_types=1);

namespace TypeSmith\Tests\Fixtures\Enums\Nested;

enum Priority: int
{
    case Low = 1;
    case Medium = 2;
    case High = 3;
}
