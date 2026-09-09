<?php
declare(strict_types=1);

/**
 * Returns the allowed lookup mode for an inquiry reference, or null when the
 * current session has no right to view it.
 */
function inquiryAccessScope(string $referenceNo, array $user, ?string $lastGuestReference): ?array
{
    if (isset($user['id']) && filter_var($user['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
        return ['user_id' => (int) $user['id']];
    }

    if ($referenceNo !== '' && hash_equals((string) $lastGuestReference, $referenceNo)) {
        return ['guest' => true];
    }

    return null;
}
