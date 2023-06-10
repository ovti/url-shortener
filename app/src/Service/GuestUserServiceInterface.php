<?php
/*
 * Guest User Interface.
 */

namespace App\Service;

use App\Entity\GuestUser;

/**
 * Interface GuestUserServiceInterface.
 */
interface GuestUserServiceInterface
{
    /**
     * Save guest user.
     *
     * @param GuestUser $guestUser GuestUser entity
     */
    public function save(GuestUser $guestUser): void;

    /**
     * Count urls created in last 24 hours for given email.
     *
     * @param string $email Email
     */
    public function countEmailsUsedInLast24Hours(string $email): int;
}
