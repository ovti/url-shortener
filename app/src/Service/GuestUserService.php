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
     */
    public function save(GuestUser $guestUser): void
    {
        // if email already exists in database, dont save it again
        if ($this->guestUserRepository->findOneByEmail($guestUser->getEmail())) {
            return;
        }

        $this->guestUserRepository->save($guestUser);
    }

    /**
     * Count urls created in last 24 hours for given email.
     *
     * @param string $email Email
     *
     * @return array
     */
    public function countEmailsUsedInLast24Hours(string $email): int
    {
        return $this->guestUserRepository->countEmailsUsedInLast24Hours($email);
    }
}
