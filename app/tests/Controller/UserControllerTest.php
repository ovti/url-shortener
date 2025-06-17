<?php

/**
 * User controller test.
 */

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UserControllerTest.
 */
class UserControllerTest extends WebTestCase
{
    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * User repository.
     */
    private UserRepository $userRepository;

    /**
     * HTTP client.
     */
    private $client;

    /**
     * Set up tests.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = $this->client->getContainer();

        $userRepository = $container->get(UserRepository::class);
        assert($userRepository instanceof UserRepository);
        $this->userRepository = $userRepository;

        $translator = $container->get(TranslatorInterface::class);
        assert($translator instanceof TranslatorInterface);
        $this->translator = $translator;

        $passwordHasher = $container->get('security.password_hasher');

        $adminUser = new User();
        $adminUser->setEmail('admin@example.com');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setPassword(
            $passwordHasher->hashPassword($adminUser, 'admin1234')
        );
        $this->userRepository->save($adminUser);

        $testUser = new User();
        $testUser->setEmail('user@example.com');
        $testUser->setRoles(['ROLE_USER']);
        $testUser->setPassword(
            $passwordHasher->hashPassword($testUser, 'user1234')
        );
        $this->userRepository->save($testUser);
    }

    /**
     * Test upgradePassword method.
     */
    public function testUpgradePassword(): void
    {
        $testUser = $this->getTestUser();
        $oldPassword = $testUser->getPassword();
        $newHashedPassword = 'newpassword123';

        $this->userRepository->upgradePassword($testUser, $newHashedPassword);

        $this->assertEquals($newHashedPassword, $testUser->getPassword());
        $this->assertNotEquals($oldPassword, $testUser->getPassword());
    }

    /**
     * Test upgradePassword method with unsupported user.
     */
    public function testUpgradePasswordWithUnsupportedUser(): void
    {
        $this->expectException(UnsupportedUserException::class);

        $unsupportedUser = $this->createMock(PasswordAuthenticatedUserInterface::class);

        $this->userRepository->upgradePassword($unsupportedUser, 'newpassword123');
    }

    /**
     * Test index page.
     */
    public function testIndex(): void
    {
        $this->client->loginUser($this->getAdminUser());
        $this->client->request('GET', '/user');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    /**
     * Test user details page.
     */
    public function testShow(): void
    {
        $this->client->loginUser($this->getAdminUser());
        $user = $this->getTestUser();
        $this->client->request('GET', '/user/'.$user->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Szczegóły użytkownika');
    }

    /**
     * Test access denied for not logged in users.
     */
    public function testAccessDeniedForAnonymousUsers(): void
    {
        $user = $this->getTestUser();
        $this->client->request('GET', '/user/'.$user->getId());
        $this->assertResponseStatusCodeSame(302);
    }

    /**
     * Test editing user password.
     */
    public function testEditPassword(): void
    {
        $this->client->loginUser($this->getAdminUser());
        $user = $this->getTestUser();
        $this->client->request('GET', '/user/'.$user->getId().'/edit/password');
        $this->assertResponseIsSuccessful();
        $submitLabel = $this->translator->trans('action.edit');
        $this->client->submitForm($submitLabel, [
            'user_password[password][first]' => 'newpassword123',
            'user_password[password][second]' => 'newpassword123',
        ]);
        $this->assertResponseRedirects('/');
    }

    /**
     * Test editing user email.
     */
    public function testEditEmail(): void
    {
        $this->client->loginUser($this->getAdminUser());
        $user = $this->getTestUser();
        $this->client->request('GET', '/user/'.$user->getId().'/edit/email');
        $this->assertResponseIsSuccessful();
        $submitLabel = $this->translator->trans('action.edit');
        $this->client->submitForm($submitLabel, [
            'user_email[email]' => 'newemail@example.com',
        ]);
        $this->assertResponseRedirects('/');
    }

    /**
     * Get admin user.
     *
     * @return User Admin user entity
     */
    private function getAdminUser(): User
    {
        return $this->userRepository->findOneBy(['email' => 'admin@example.com']);
    }

    /**
     * Get test user.
     *
     * @return User Test user entity
     */
    private function getTestUser(): User
    {
        return $this->userRepository->findOneBy(['email' => 'user@example.com']);
    }
}
