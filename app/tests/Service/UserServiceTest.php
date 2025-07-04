<?php

/**
 * User service test.
 */

namespace App\Tests\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserServiceTest.
 */
class UserServiceTest extends TestCase
{
    private UserService $userService;
    private UserRepository $userRepository;
    private PaginatorInterface $paginator;
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $this->userService = new UserService(
            $this->userRepository,
            $this->paginator,
            $this->passwordHasher
        );
    }

    /**
     * Test getPaginatedList method returns expected a pagination object.
     */
    public function testGetPaginatedList(): void
    {
        $page = 1;
        $paginationMock = $this->createMock(PaginationInterface::class);

        $this->paginator
            ->expects($this->once())
            ->method('paginate')
            ->willReturn($paginationMock);

        $this->userRepository
            ->expects($this->once())
            ->method('queryAll')
            ->willReturn($this->createMock(QueryBuilder::class));

        $result = $this->userService->getPaginatedList($page);

        $this->assertSame($paginationMock, $result);
    }

    /**
     * Test save method hashes password and persists the user.
     */
    public function testSave(): void
    {
        $user = new User();
        $user->setPassword('password123');

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user);

        $this->passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($user, 'password123')
            ->willReturn('hashedPassword123');

        $this->userService->save($user);

        $this->assertSame('hashedPassword123', $user->getPassword());
    }

    /**
     * Test findOneBy method returns a user by email.
     */
    public function testFindOneBy(): void
    {
        $email = 'test@example.com';
        $userMock = $this->createMock(User::class);

        $this->userRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $email])
            ->willReturn($userMock);

        $result = $this->userService->findOneBy($email);

        $this->assertSame($userMock, $result);
    }
}
