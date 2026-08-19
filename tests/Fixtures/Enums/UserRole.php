<?php

declare(strict_types=1);

namespace TypeSmith\Tests\Fixtures\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case User = 'user';
}
