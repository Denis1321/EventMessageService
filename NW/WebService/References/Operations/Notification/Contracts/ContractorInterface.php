<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification\Contracts;

interface ContractorInterface
{
    /**
     * Возвращает полное имя контрактора.
     *
     * @return string
     */
    public function getFullName(): string;
}