<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification\Types;

enum NotificationType: int
{
    case New = 1;
    case Change = 2;
}
