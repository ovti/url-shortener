<?php

/**
 * Security controller test.
 */

namespace App\Tests\Controller;

use App\Controller\SecurityController;
use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SecurityControllerTest.
 */
class SecurityControllerTest extends WebTestCase
{
    /**
     * Test client.
     */
    private KernelBrowser $httpClient;
    private UserRepository $userRepository;
    private TranslatorInterface $translator;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        $this->httpClient = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->translator = static::getContainer()->get(TranslatorInterface::class);
    }

    /**
     * Test login page is accessible.
     */
    public function testLoginPageIsAccessible(): void
    {
        $this->httpClient->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Zaloguj się');
    }

    /**
     * Test login page is accessible when already logged in.
     */
    public function testLoginRedirectsWhenAlreadyLoggedIn(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);

        $this->httpClient->request('GET', '/login');

        $this->assertResponseRedirects('/url/list');
    }

    /**
     * Test login fails with invalid credentials.
     */
    public function testLoginFailsWithInvalidCredentials(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);

        $this->httpClient->request('GET', '/login');
        $submitLabel = $this->translator->trans('label.sign_in');
        $this->httpClient->submitForm($submitLabel, [
            'email' => $user->getEmail(),
            'password' => 'wrongpassword',
        ]);

        $this->assertResponseRedirects('/login');

        $this->httpClient->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    /**
     * Test login succeeds with valid credentials.
     */
    public function testLoginSucceedsWithValidCredentials(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value], 'testpassword');

        $this->httpClient->request('GET', '/login');
        $submitLabel = $this->translator->trans('label.sign_in');
        $this->httpClient->submitForm($submitLabel, [
            'email' => $user->getEmail(),
            'password' => 'testpassword',
        ]);

        $this->assertResponseRedirects();
    }

    /**
     * Test logout functionality.
     */
    public function testLogout(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($user);
        $this->httpClient->request('GET', '/logout');
        $this->assertResponseRedirects('/login');

        $this->httpClient->followRedirect();
        $this->assertSelectorTextContains('h1', 'Zaloguj się');
    }

    /**
     * Test getLoginUrl method.
     *
     * @throws \ReflectionException
     */
    public function testGetLoginUrl(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $authenticator = new LoginFormAuthenticator($urlGenerator);

        $reflectionMethod = new \ReflectionMethod(LoginFormAuthenticator::class, 'getLoginUrl');

        $result = $reflectionMethod->invoke($authenticator, $this->createMock(Request::class));

        $this->assertSame('', $result);
    }

    /**
     * Test logout method throws LogicException.
     */
    public function testLogoutThrowsLogicException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('This method can be blank - it will be intercepted by the logout key on your firewall.');

        $controller = new SecurityController();
        $controller->logout();
    }

    /**
     * Test logout redirects to login page.
     */
    public function testLogoutRedirectsToLoginPage(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);

        $this->httpClient->request('GET', '/logout');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Create user.
     *
     * @param array  $roles         User roles
     * @param string $plainPassword Plain password
     *
     * @return User User entity
     */
    private function createUser(array $roles, string $plainPassword = 'password'): User
    {
        $passwordHasher = static::getContainer()->get('security.password_hasher');
        $user = new User();
        $user->setEmail('user_'.uniqid().'@example.com');
        $user->setRoles($roles);
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                $plainPassword
            )
        );
        $this->userRepository->save($user);

        return $user;
    }
}
