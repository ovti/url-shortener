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
}