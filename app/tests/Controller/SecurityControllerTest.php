<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityControllerTest extends WebTestCase
{
    private $client;
    private UserRepository $userRepository;
    private TranslatorInterface $translator;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = $this->client->getContainer()->get(UserRepository::class);
        $this->translator = $this->client->getContainer()->get(TranslatorInterface::class);

        $testUser = new User();
        $testUser->setEmail('testuser@example.com');
        $testUser->setRoles(['ROLE_USER']);
        $testUser->setPassword(
            $this->client->getContainer()->get('security.password_hasher')->hashPassword($testUser, 'testpassword')
        );
        $this->userRepository->save($testUser);
    }

    public function testLoginPageIsAccessible(): void
    {
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Zaloguj się');
    }

    public function testLoginRedirectsWhenAlreadyLoggedIn(): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'testuser@example.com']);
        $this->client->loginUser($user);

        $this->client->request('GET', '/login');

        $this->assertResponseRedirects('/url/list');
    }

    public function testLoginFailsWithInvalidCredentials(): void
    {
        $this->client->request('GET', '/login');
        $submitLabel = $this->translator->trans('label.sign_in');
        $this->client->submitForm($submitLabel, [
            'email' => 'testuser@example.com',
            'password' => 'wrongpassword'
        ]);

        $this->assertResponseRedirects('/login');

        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testLoginSucceedsWithValidCredentials(): void
    {
        $this->client->request('GET', '/login');
        $submitLabel = $this->translator->trans('label.sign_in'); // Pobranie przetłumaczonej etykiety
        $this->client->submitForm($submitLabel, [
            'email' => 'testuser@example.com',
            'password' => 'testpassword'
        ]);

        $this->assertResponseRedirects();
    }

    public function testLogout(): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'admin1@example.com']);
        $this->client->loginUser($user);
        $this->client->request('GET', '/logout');
        $this->assertResponseRedirects('/login');

        $this->client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Zaloguj się');


    }

    public function testGetLoginUrl(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $authenticator = new LoginFormAuthenticator($urlGenerator);

        $reflectionMethod = new \ReflectionMethod(LoginFormAuthenticator::class, 'getLoginUrl');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invoke($authenticator, $this->createMock(\Symfony\Component\HttpFoundation\Request::class));

        $this->assertSame('', $result);
    }

    public function testLogoutThrowsLogicException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('This method can be blank - it will be intercepted by the logout key on your firewall.');

        $controller = new \App\Controller\SecurityController();
        $controller->logout();
    }
    public function testLogoutRedirectsToLoginPage(): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'testuser@example.com']);
        $this->client->loginUser($user);

        $this->client->request('GET', '/logout');

        $this->assertResponseRedirects('/login');
    }
}