<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification\Entity;

use NW\WebService\References\Operations\Notification\Contracts\ContractorInterface;

abstract class Contractor implements ContractorInterface
{
    public function __construct(
        public readonly int $id,
        public readonly string $name
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getFullName(): string
    {
        return $this->name . ' ' . $this->id;
    }
}
