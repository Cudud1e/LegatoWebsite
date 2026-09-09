<?php
declare(strict_types=1);

ini_set('session.save_path', sys_get_temp_dir());
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/inquiry_access.php';

function expect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$token = csrfToken();
expect(verifyCsrfToken($token), 'CSRF token should validate.');
expect(!verifyCsrfToken('invalid-token'), 'Invalid CSRF token should fail.');
expect(isValidDateOnOrAfterToday((new DateTimeImmutable('today'))->format('Y-m-d')), 'Today should be an allowed event date.');
expect(!isValidDateOnOrAfterToday((new DateTimeImmutable('yesterday'))->format('Y-m-d')), 'Past event dates should fail.');
expect(!isValidDateOnOrAfterToday('2026-02-30'), 'Invalid dates should fail.');
expect(isValidPhoneNumber('+63 912 345 6789'), 'A valid phone number should pass.');
expect(!isValidPhoneNumber('abc'), 'An invalid phone number should fail.');
expect(profileValidationError('Client Name', 'Client', '+63 912 345 6789', 'Dumaguete') === null, 'Valid profile input should pass.');
expect(profileValidationError('', 'Client', '+63 912 345 6789', 'Dumaguete') !== null, 'Missing profile name should fail.');
expect(inquiryAccessScope('LGT-2026-1234', ['id' => 7], null) === ['user_id' => 7], 'A signed-in user should be scoped to their own ID.');
expect(inquiryAccessScope('LGT-2026-1234', [], 'LGT-2026-1234') === ['guest' => true], 'A guest may view only their most recent reference.');
expect(inquiryAccessScope('LGT-2026-1234', [], 'LGT-2026-9999') === null, 'A guest must not view another reference.');

echo "All core tests passed.\n";
