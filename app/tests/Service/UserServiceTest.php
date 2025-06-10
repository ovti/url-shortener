<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private UserRepository $userRepository;
    private PaginatorInterface $paginator;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $this->userService = new UserService($this->userRepository, $this->paginator, $this->passwordHasher);
    }

    public function testGetPaginatedList(): void
    {
        $page = 1;
        $paginationMock = $this->createMock(\Knp\Component\Pager\Pagination\PaginationInterface::class);

        $this->paginator
            ->expects($this->once())
            ->method('paginate')
            ->willReturn($paginationMock);

        $this->userRepository
            ->expects($this->once())
            ->method('queryAll')
            ->willReturn($this->createMock(\Doctrine\ORM\QueryBuilder::class));

        $result = $this->userService->getPaginatedList($page);

        $this->assertSame($paginationMock, $result);
    }

    public function testSave(): void
    {
        $user = new User();
        $user->setPassword('plainPassword123');

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user);

        $this->passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($user, 'plainPassword123')
            ->willReturn('hashedPassword123');

        $this->userService->save($user);

        $this->assertSame('hashedPassword123', $user->getPassword());
    }

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