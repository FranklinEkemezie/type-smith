<?php

declare(strict_types=1);

namespace TypeSmith\Tests\Fixtures\Enums;

enum Status: string
{
    case Draft = 'draft';
    case Published = 'published';
}
