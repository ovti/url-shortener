<?php

/**
 * Guest user service test.
 */

namespace App\Tests\Service;

use App\Entity\GuestUser;
use App\Repository\GuestUserRepository;
use App\Service\GuestUserService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class GuestUserServiceTest.
 */
class GuestUserServiceTest extends TestCase
{
    private GuestUserRepository $guestUserRepository;
    private GuestUserService $guestUserService;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        $this->guestUserRepository = $this->createMock(GuestUserRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->guestUserService = new GuestUserService($this->guestUserRepository);
    }

    /**
     * Test saving a new guest user.
     */
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

    /**
     * Test saving an existing guest user should do nothing.
     */
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

    /**
     * Test counting emails used in the last 24 hours.
     */
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

    /**
     * Test the save method inside the GuestUserRepository.
     */
    public function testSaveMethodInRepository(): void
    {
        $guestUser = new GuestUser();
        $guestUser->setEmail('test@example.com');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($guestUser);

        $entityManager->expects($this->once())
            ->method('flush');

        $guestUserRepository = $this->createPartialMock(
            GuestUserRepository::class,
            []
        );

        $reflectionProperty = new \ReflectionProperty(GuestUserRepository::class, '_em');
        $reflectionProperty->setValue($guestUserRepository, $entityManager);

        $guestUserRepository->save($guestUser);
    }

    /**
     * Test findOneByEmail method in the repository.
     */
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

    /**
     * Test getId method in the GuestUser entity.
     */
    public function testGetIdMethodInEntity(): void
    {
        $guestUser = new GuestUser();
        $reflection = new \ReflectionClass($guestUser);
        $property = $reflection->getProperty('id');
        $property->setValue($guestUser, 1);

        $this->assertSame(1, $guestUser->getId());
    }
}
