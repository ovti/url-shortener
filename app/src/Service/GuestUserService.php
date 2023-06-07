<?php
/**
 * Guest User service.
 */

namespace App\Service;


use App\Entity\GuestUser;
use App\Repository\GuestUserRepository;

/**
 * Class GuestUserService.
 */
class GuestUserService implements GuestUserServiceInterface
{

    /**
     * GuestUser repository.
     */
    private GuestUserRepository $guestUserRepository;

    /**
     * GuestUserService constructor.
     *
     * @param GuestUserRepository $guestUserRepository GuestUser repository
     */
    public function __construct(GuestUserRepository $guestUserRepository)
    {
        $this->guestUserRepository = $guestUserRepository;
    }

    /**
     * Save guest user.
     *
     * @param \App\Entity\GuestUser $guestUser GuestUser entity
     *
     * @return void
     */
    public function save(GuestUser $guestUser): void
    {
        $this->guestUserRepository->save($guestUser);
    }
}
