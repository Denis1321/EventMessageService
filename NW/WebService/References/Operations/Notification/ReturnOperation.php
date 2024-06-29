<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification;

use Exception;
use NW\WebService\References\Operations\Notification\Contracts\ReferencesOperationInterface;
use NW\WebService\References\Operations\Notification\DataObjects\ClientTemplateMessageDTO;
use NW\WebService\References\Operations\Notification\DataObjects\EventInformation;
use NW\WebService\References\Operations\Notification\Factories\ContractorFactory;
use NW\WebService\References\Operations\Notification\Types\MessageType;
use NW\WebService\References\Operations\Notification\Types\NotificationEventStatusType;
use NW\WebService\References\Operations\Notification\Types\NotificationType;
use NW\WebService\References\Operations\Notification\Types\StatusType;

/**
 * Опишу кратко почему были сделаны такие изменения
 * (этот код и дальше можно улучшать, но ввиду того что я не могу задать вопросы к заказчику
 * и у меня недостаточно вводной информации остановился на этой реализации):
 * ввиду того что я не знаю что приходит в этот сервис - выпилил код в который никогда не зайдет сервис, а именно
 * в отправку уведомления клиенту при изменении статуса,
 * также вынес возвращаемый шаблон в DTO комментарии по нему смотреть в {@see ClientTemplateMessageDTO}
 * Остальные изменения можно обсудить при дальнейшем взаимодействии, если появятся вопросы.
 *
 * По поводу качества когда и его назначения:
 *
 * Думаю что по количеству необходимых изменений для валидации, соответствия типов и потенциально возможных ошибок до рефакторинга
 * видно что данные могли быть переданы в совершенно неизвестном виде из этого сервиса, сейчас хотя бы выпадут эксепшены в большинстве мест,
 * однако, полагаю если у нас на уровнях выше нет необходимой обработки данных эксепшенов, то клиент с которого пришел запрос получит трассировку
 * об ошибке либо просто информацию о том что произошла ошибка с кодом, а не то что мы ожидаем т.к.
 * для клиента обрабатывается лишь ситуация с некорректным sellerId
 * так что все вызовы эксепшенов можно также заменить на историю с возвратом массива с указанным сообщением об ошибке.
 * Также ни о каких стандартах PSR тут речи и не шло, что удручает в этом коде.
 * Можно долго продолжать, но резюмируя - качество когда было на крайне низком уровне, да и про low Coupling речи также не шло.
 *
 * Насчет назначения - исходя из того как названы ключи в шаблоне могу предположить что после того как мы посредством MessagesClient
 * отправляли сообщение сотруднику на почту (предполагалось, что еще и клиенту должно было уходить сообщение об изменениях)
 * дальше эта информация (событие) должна была уйти в какой-нибудь Bitrix24, больше я нигде такого формирования не видел
 */
class ReturnOperation implements ReferencesOperationInterface
{
    /**
     * @var array Массив ошибок
     */
    private array $errors = [];

    /**
     * @param Request $request
     * @param ContractorFactory $contractorFactory
     */
    public function __construct(
        private readonly Request $request,
        private readonly ContractorFactory $contractorFactory
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function doOperation(): array
    {
        $requestData = $this->getValidatedRequestData();

        if (!$requestData) {
            return $this->getErrorMessageData()->toArray();
        }

        $templateData = $this->createTemplateEvent($requestData);

        $emailFrom = get_reseller_email_from();
        $emails = get_employee_emails();

        $result = $this->getMessageData();
        foreach ($emails as $email) {
            $this->sendMessageSeller($emailFrom, $email, $templateData, $requestData->seller->id);
            $result->setNotificationEmployeeByEmail(true);
        }

        return $result->toArray();
    }

    /**
     * Отправляет сообщение сотруднику с информацией о продавце.
     *
     * @param string $emailFrom
     * @param string $emailTo
     * @param array $template
     * @param int $sellerId
     *
     * @return void
     */
    private function sendMessageSeller(string $emailFrom, string $emailTo, array $template, int $sellerId): void
    {
        MessagesClient::sendMessage([
            MessageType::Email->value => [
                'emailFrom' => $emailFrom,
                'emailTo' => $emailTo,
                'subject' => __('complaintEmployeeEmailSubject', $template, $sellerId),
                'message' => __('complaintEmployeeEmailBody', $template, $sellerId),
            ],
        ], $sellerId, NotificationEventStatusType::ChangeReturnStatus);
    }

    /**
     * Возвращает провалидированные данные.
     *
     * @return EventInformation|false
     *
     * @throws Exception
     */
    private function getValidatedRequestData(): EventInformation|false
    {
        $data = (array)$this->request->getRequestValue('data');
        $notificationType = $data['notificationType'] ?? null;
        $resellerId = $data['resellerId'] ?? null;
        $resellerName = (string)$data['resellerName'];
        $clientId = $data['clientId'] ?? null;
        $clientName = (string)$data['clientName'];
        $creatorId = $data['creatorId'] ?? null;
        $creatorName = (string)$data['creatorName'];
        $expertId = $data['expertId'] ?? null;
        $expertName = (string)$data['expertName'];

        if ($resellerId === null) {
            $this->errors['reseller'] = 'Empty resellerId';

            return false;
        }

        match (true) {
            empty($notificationType) => throw new Exception('Notification type is empty', 400),
            $clientId === null => throw new Exception('Client not found!', 400),
            $creatorId === null => throw new Exception('Creator not found!', 400),
            $expertId === null => throw new Exception('Expert not found!', 400),
            default => null
        };

        $notificationType = NotificationType::from((int)$notificationType);

        return new EventInformation(
            $this->contractorFactory->buildSeller($resellerId, $resellerName),
            $this->contractorFactory->buildClient($clientId, $clientName),
            $this->contractorFactory->buildEmployee($creatorId, $creatorName),
            $this->contractorFactory->buildEmployee($expertId, $expertName),
            $notificationType,
            $this->createDifferences($notificationType, $resellerId, $data['differences'] ?? []),
            (int)$data['complaintId'],
            (string)$data['complaintNumber'],
            (int)$data['consumptionId'],
            (string)$data['consumptionNumber'],
            (string)$data['agreementNumber'],
            (string)$data['date']
        );
    }

    /**
     * Возвращает шаблон для сообщения клиента.
     *
     * @return ClientTemplateMessageDTO
     */
    private function getMessageData(): ClientTemplateMessageDTO
    {
        return new ClientTemplateMessageDTO();
    }

    /**
     * Возвращает сообщение об ошибке для клиента.
     *
     * @return ClientTemplateMessageDTO
     */
    private function getErrorMessageData(): ClientTemplateMessageDTO
    {
        return $this->getMessageData()
            ->setMessage($this->errors['reseller'] ?? '');
    }

    /**
     * Создает differences в зависимости от типа уведомления.
     *
     * @param NotificationType $notificationType
     * @param int $resellerId
     * @param array $dataDifferences
     *
     * @return array|string
     */
    private function createDifferences(
        NotificationType $notificationType,
        int $resellerId,
        array $dataDifferences
    ): string|array {
        $differences = '';
        if ($notificationType === NotificationType::New) {
            $differences = __('NewPositionAdded', null, $resellerId);
        }

        if ($notificationType === NotificationType::Change && !empty($dataDifferences)) {
            $differences = __('PositionStatusHasChanged', [
                'FROM' => StatusType::from((int)$dataDifferences['differences']['from'])->name,
                'TO' => StatusType::from((int)$dataDifferences['differences']['to'])->name,
            ], $resellerId);
        }

        return $differences;
    }

    /**
     * Проверяет шаблон уведомления на существование всех аргументов.
     *
     * @param array $templateData
     *
     * @return void
     *
     * @throws Exception
     */
    private function validateTemplate(array $templateData): void
    {
        foreach ($templateData as $key => $tempData) {
            if (empty($tempData)) {
                throw new Exception("Template Data ({$key}) is empty!", 500);
            }
        }
    }

    /**
     * Создает шаблон уведомления.
     *
     * @param EventInformation $eventInformation
     *
     * @return array
     *
     * @throws Exception
     */
    private function createTemplateEvent(EventInformation $eventInformation): array
    {
        $templateData = [
            'COMPLAINT_ID' => $eventInformation->complaintId,
            'COMPLAINT_NUMBER' => $eventInformation->complaintNumber,
            'CREATOR_ID' => $eventInformation->creator->id,
            'CREATOR_NAME' => $eventInformation->creator->getFullName(),
            'EXPERT_ID' => $eventInformation->expert->id,
            'EXPERT_NAME' => $eventInformation->expert->getFullName(),
            'CLIENT_ID' => $eventInformation->client->id,
            'CLIENT_NAME' => $eventInformation->client->getFullName(),
            'CONSUMPTION_ID' => $eventInformation->consumptionId,
            'CONSUMPTION_NUMBER' => $eventInformation->consumptionNumber,
            'AGREEMENT_NUMBER' => $eventInformation->consumptionNumber,
            'DATE' => $eventInformation->date,
            'DIFFERENCES' => $eventInformation->differences,
        ];

        $this->validateTemplate($templateData);

        return $templateData;
    }
}
