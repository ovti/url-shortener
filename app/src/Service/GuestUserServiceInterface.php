<?php
/*
 * Guest User Interface.
 */

namespace App\Service;

use App\Entity\GuestUser;
use App\Repository\GuestUserRepository;

/**
 * Interface GuestUserServiceInterface.
 */
interface GuestUserServiceInterface
{
    /**
     * Save guest user.
     *
     * @param \App\Entity\GuestUser $guestUser GuestUser entity
     *
     * @return void
     */
    public function save(GuestUser $guestUser): void;

    /**
     * Count urls created in last 24 hours for given email.
     *
     * @param string $email Email
     *
     * @return array
     */
    public function countUrlsCreatedInLast24Hours(string $email): int;
}