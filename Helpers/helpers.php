<?php

declare(strict_types=1);

/**
 * Возвращает почту reseller'а
 *
 * @return string
 */
function get_reseller_email_from(): string
{
    return 'contractor@example.com';
}

/**
 * Возвращает массив email'ов сотрудников.
 *
 * @return string[]
 */
function get_employee_emails(): array
{
    return ['someemeil@example.com', 'someemeil2@example.com'];
}