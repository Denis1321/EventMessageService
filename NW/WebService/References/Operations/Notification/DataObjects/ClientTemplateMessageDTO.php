<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification\DataObjects;

/**
 * Параметры isSent и notificationClientByEmail заданы так потому что в процессе работы сервиса они никак не меняются
 * т.к. отсутствует информация о сущности Client, а именно о его почте ('email'), скорее всего изначально она должна,
 * как и Seller/Employee, должна создаваться по идентификатору из базы/кэша и т.д. где будет необходимая мета-информация.
 * А возможно это также приходит в $_REQUEST, если так, то не будет проблем добавить эту логику вновь
 * Но оставил их потому что неизвестно как в дальнейшем обрабатывается информация из этого сервиса и возможно это будет критично.
 */
final class ClientTemplateMessageDTO
{
    /**
     * @var bool Уведомление клиенту по почте
     */
    private readonly bool $notificationClientByEmail;

    /**
     * @var bool Уведомление отправлено
     */
    private readonly bool $isSent;

    /**
     * @param bool $notificationEmployeeByEmail Уведомление сотруднику по почте
     * @param string $message Сообщение
     */
    public function __construct(
        private bool $notificationEmployeeByEmail = false,
        private string $message = ''
    ) {
        $this->isSent = false;
        $this->notificationClientByEmail = false;
    }

    /**
     * Устанавливает тип уведомления - сотруднику по почте.
     *
     * @param bool $notificationEmployeeByEmail
     *
     * @return $this
     */
    public function setNotificationEmployeeByEmail(bool $notificationEmployeeByEmail): self
    {
        $this->notificationEmployeeByEmail = $notificationEmployeeByEmail;

        return $this;
    }

    /**
     * Устанавливает сообщение для пользователя.
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Возвращает шаблон в виде массива.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'notificationEmployeeByEmail' => $this->notificationEmployeeByEmail,
            'notificationClientByEmail' => $this->notificationClientByEmail,
            'notificationClientBySms' => [
                'isSent' => $this->isSent,
                'message' => $this->message,
            ],
        ];
    }
}
