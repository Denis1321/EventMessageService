<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification\Contracts;

interface RequestInterface
{
    /**
     * Возвращает значение из запроса.
     *
     * @param int|string $pName
     *
     * @return mixed
     */
    public function getRequestValue(int|string $pName): mixed;
}
