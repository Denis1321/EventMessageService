<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification\DataObjects;

use NW\WebService\References\Operations\Notification\Entity\Client;
use NW\WebService\References\Operations\Notification\Entity\Employee;
use NW\WebService\References\Operations\Notification\Entity\Seller;
use NW\WebService\References\Operations\Notification\Types\NotificationType;

final class EventInformation
{
    /**
     * @param Seller $seller Сущность продавца
     * @param Client $client Сущность клиента
     * @param Employee $creator Сущность создателя
     * @param Employee $expert Сущность эксперта
     * @param NotificationType $notificationType Тип уведомления
     * @param string|array $differences Информация о различиях?
     * @param int $complaintId Идентификатор жалобы
     * @param string $complaintNumber Номер жалобы
     * @param int $consumptionId Идентификатор расхода
     * @param string $consumptionNumber Номер расхода
     * @param string $agreementNumber Номер договора
     * @param string $date Дата
     */
    public function __construct(
        public readonly Seller $seller,
        public readonly Client $client,
        public readonly Employee $creator,
        public readonly Employee $expert,
        public readonly NotificationType $notificationType,
        public readonly string|array $differences,
        public readonly int $complaintId,
        public readonly string $complaintNumber,
        public readonly int $consumptionId,
        public readonly string $consumptionNumber,
        public readonly string $agreementNumber,
        public readonly string $date,
    ) {
    }
}
