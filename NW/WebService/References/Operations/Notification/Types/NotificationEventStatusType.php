<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification\Types;

enum NotificationEventStatusType: string
{
    case ChangeReturnStatus = 'changeReturnStatus';
    case NewReturnStatus = 'newReturnStatus';
}
