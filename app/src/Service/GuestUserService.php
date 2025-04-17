<?php

/**
 * Guest User service.
 */

namespace App\Service;

use App\Entity\GuestUser;
use App\Repository\GuestUserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * Class GuestUserService.
 */
class GuestUserService implements GuestUserServiceInterface
{
    /**
     * GuestUserService constructor.
     *
     * @param GuestUserRepository $guestUserRepository GuestUser repository
     */
    public function __construct(private readonly GuestUserRepository $guestUserRepository)
    {
    }

    /**
     * Save guest user.
     *
     * @param GuestUser $guestUser GuestUser entity
     */
    public function save(GuestUser $guestUser): void
    {
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
     * @return int Result
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countEmailsUsedInLast24Hours(string $email): int
    {
        return $this->guestUserRepository->countEmailsUsedInLast24Hours($email);
    }
}
