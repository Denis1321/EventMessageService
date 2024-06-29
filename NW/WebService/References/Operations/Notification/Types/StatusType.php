<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification\Types;

enum StatusType: int
{
    case Completed = 0;
    case Pending = 1;
    case Rejected = 2;
}

