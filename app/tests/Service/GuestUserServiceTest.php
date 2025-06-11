<?php

namespace App\Tests\Service;

use App\Entity\GuestUser;
use App\Repository\GuestUserRepository;
use App\Service\GuestUserService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use PHPUnit\Framework\TestCase;
use App\Repository\GuestUserRepositoryInterface;
use App\Entity\GuestUserInterface;
use App\Repository\GuestUserRepositoryInterface as GuestUserRepositoryInterfaceAlias;
use App\Entity\GuestUserInterface as GuestUserInterfaceAlias;
use Doctrine\Persistence\ManagerRegistry;

class GuestUserServiceTest extends TestCase
{
    private GuestUserRepository $guestUserRepository;
    private GuestUserService $guestUserService;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->guestUserRepository = $this->createMock(GuestUserRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->guestUserService = new GuestUserService($this->guestUserRepository);
    }

    public function testSaveNewGuestUser(): void
    {
        $guestUser = new GuestUser();
        $guestUser->setEmail('test@example.com');

        $this->guestUserRepository
            ->expects($this->once())
            ->method('findOneByEmail')
            ->with('test@example.com')
            ->willReturn(null);

        $this->guestUserRepository
            ->expects($this->once())
            ->method('save')
            ->with($guestUser);

        $this->guestUserService->save($guestUser);
    }

    public function testSaveExistingGuestUser(): void
    {
        $guestUser = new GuestUser();
        $guestUser->setEmail('test@example.com');

        $this->guestUserRepository
            ->expects($this->once())
            ->method('findOneByEmail')
            ->with('test@example.com')
            ->willReturn($guestUser);

        $this->guestUserRepository
            ->expects($this->never())
            ->method('save');

        $this->guestUserService->save($guestUser);
    }

    public function testCountEmailsUsedInLast24Hours(): void
    {
        $email = 'test@example.com';

        $this->guestUserRepository
            ->expects($this->once())
            ->method('countEmailsUsedInLast24Hours')
            ->with($email)
            ->willReturn(5);

        $result = $this->guestUserService->countEmailsUsedInLast24Hours($email);

        $this->assertSame(5, $result);
    }

    public function testCountEmailsUsedInLast24HoursThrowsNoResultException(): void
    {
        $email = 'test@example.com';

        $this->guestUserRepository
            ->expects($this->once())
            ->method('countEmailsUsedInLast24Hours')
            ->with($email)
            ->willThrowException(new NoResultException());

        $this->expectException(NoResultException::class);

        $this->guestUserService->countEmailsUsedInLast24Hours($email);
    }

    public function testCountEmailsUsedInLast24HoursThrowsNonUniqueResultException(): void
    {
        $email = 'test@example.com';

        $this->guestUserRepository
            ->expects($this->once())
            ->method('countEmailsUsedInLast24Hours')
            ->with($email)
            ->willThrowException(new NonUniqueResultException());

        $this->expectException(NonUniqueResultException::class);

        $this->guestUserService->countEmailsUsedInLast24Hours($email);
    }
    public function testSaveMethodInRepository(): void
{
    $guestUser = new GuestUser();
    $guestUser->setEmail('test@example.com');

    $managerRegistryMock = $this->createMock(ManagerRegistry::class);

    $guestUserRepository = new class($this->entityManager, $managerRegistryMock) extends GuestUserRepository {
        public function __construct(
            private EntityManagerInterface $entityManager,
            ManagerRegistry $managerRegistry
        ) {
            parent::__construct($managerRegistry);
        }

        public function save(GuestUser $guestUser): void
        {
            $this->entityManager->persist($guestUser);
            $this->entityManager->flush();
        }
    };

    $this->entityManager
        ->expects($this->once())
        ->method('persist')
        ->with($guestUser);

    $this->entityManager
        ->expects($this->once())
        ->method('flush');

    $guestUserRepository->save($guestUser);
}


    public function testFindOneByEmailMethodInRepository(): void
    {
        $email = 'test@example.com';
        $guestUser = new GuestUser();
        $guestUser->setEmail($email);

        $this->guestUserRepository
            ->expects($this->once())
            ->method('findOneByEmail')
            ->with($email)
            ->willReturn($guestUser);

        $result = $this->guestUserRepository->findOneByEmail($email);

        $this->assertSame($guestUser, $result);
    }

    public function testGetIdMethodInEntity(): void
    {
        $guestUser = new GuestUser();
        $reflection = new \ReflectionClass($guestUser);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($guestUser, 1);

        $this->assertSame(1, $guestUser->getId());
    }
}