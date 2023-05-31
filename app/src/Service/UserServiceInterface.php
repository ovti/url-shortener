<?php
/*
 * User Service Interface.
 */

namespace App\Service;

use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Interface UserServiceInterface.
 */
interface UserServiceInterface
{
    /**
     * Create paginated list.
     *
     * @param int $page Page number
     *
     * @return \Knp\Component\Pager\Pagination\PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Save user.
     *
     * @param \App\Entity\User $user User entity
     *
     * @return void
     */
    public function save(User $user): void;

    /**
     * Delete user.
     *
     * @param \App\Entity\User $user User entity
     *
     * @return void
     */
    public function delete(User $user): void;

    /**
     * Find one by email.
     *
     * @param string $email User email
     *
     * @return \App\Entity\User|null User entity
     */
    public function findOneBy(string $email): ?User;
}