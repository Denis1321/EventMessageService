<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification\Contracts;

interface ReferencesOperationInterface
{
    /**
     * Осуществляет выполнение операции.
     *
     * @return array
     */
    public function doOperation(): array;
}
