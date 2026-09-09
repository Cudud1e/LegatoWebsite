<?php
declare(strict_types=1);

function isValidDateOnOrAfterToday(string $value): bool
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    $errors = DateTimeImmutable::getLastErrors();

    return $date !== false
        && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
        && $date->format('Y-m-d') === $value
        && $date >= new DateTimeImmutable('today');
}

function isValidPhoneNumber(string $value): bool
{
    return strlen($value) <= 30
        && preg_match('/^[0-9+() .-]{7,30}$/', $value) === 1
        && preg_match_all('/\d/', $value) >= 7;
}

function profileValidationError(string $fullName, string $nickname, string $phone, string $location): ?string
{
    if ($fullName === '' || $nickname === '' || $phone === '' || $location === '') {
        return 'Please complete all profile fields.';
    }
    if (strlen($fullName) > 150 || strlen($nickname) > 80 || strlen($location) > 255) {
        return 'One or more profile fields are too long.';
    }
    if (!isValidPhoneNumber($phone)) {
        return 'Enter a valid phone number.';
    }

    return null;
}
