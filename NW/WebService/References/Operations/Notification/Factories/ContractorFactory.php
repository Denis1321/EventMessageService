<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification\Factories;

use NW\WebService\References\Operations\Notification\Entity\Client;
use NW\WebService\References\Operations\Notification\Entity\Employee;
use NW\WebService\References\Operations\Notification\Entity\Seller;

class ContractorFactory
{
    /**
     * Создает клиента.
     *
     * @param int $id
     * @param string $name
     *
     * @return Client
     */
    public function buildClient(int $id, string $name): Client
    {
        return new Client($id, $name);
    }

    /**
     * Создает продавца.
     *
     * @param int $id
     * @param string $name
     *
     * @return Seller
     */
    public function buildSeller(int $id, string $name): Seller
    {
        return new Seller($id, $name);
    }

    /**
     * Создает сотрудника.
     *
     * @param int $id
     * @param string $name
     *
     * @return Employee
     */
    public function buildEmployee(int $id, string $name): Employee
    {
        return new Employee($id, $name);
    }
}
