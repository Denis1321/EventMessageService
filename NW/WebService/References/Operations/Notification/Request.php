<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification;

use NW\WebService\References\Operations\Notification\Contracts\RequestInterface;

class Request implements RequestInterface
{
    /**
     * {@inheritDoc}
     */
    public function getRequestValue(int|string $pName): mixed
    {
        return $_REQUEST[$pName];
    }
}