<?php
/**
 * User service.
 */

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


/**
 * Class UserService.
 */
class UserService implements UserServiceInterface
{

    private UserRepository $userRepository;

    private PaginatorInterface $paginator;

    private UserPasswordHasherInterface $passwordHasher;


    /**
     * UserService constructor.
     *
     * @param \App\Repository\UserRepository                                        $userRepository  User repository
     * @param \Knp\Component\Pager\PaginatorInterface                               $paginator       Paginator
     * @param \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $passwordHasher  Password hasher
     */
    public function __construct(UserRepository $userRepository, PaginatorInterface $paginator, UserPasswordHasherInterface $passwordHasher)
    {
        $this->userRepository = $userRepository;
        $this->paginator = $paginator;
        $this->passwordHasher = $passwordHasher;

    }

    /**
     * Create paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->userRepository->queryAll(),
            $page,
            UserRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save user.
     *
     * @param \App\Entity\User $user User entity
     */
    public function save(User $user): void
    {
        if (null === $user->getId()) {
            $user->setPassword(
                $this->passwordHasher->hashPassword(
                    $user,
                    $user->getPassword()
                )
            );
            $user->setRoles(['ROLE_USER']);
        }

        $this->userRepository->save($user);
    }

    /**
     * Delete user.
     *
     * @param \App\Entity\User $user User entity
     */
    public function delete(User $user): void
    {
        $this->userRepository->delete($user);
    }

    /**
     * Find one by email.
     *
     * @param string $email User email
     *
     * @return \App\Entity\User|null User entity
     */
    public function findOneBy(string $email): ?User
    {
        return $this->userRepository->findOneBy(['email' => $email]);
    }
}
