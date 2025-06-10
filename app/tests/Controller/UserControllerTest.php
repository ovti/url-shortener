<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;


class UserControllerTest extends WebTestCase
{

    private TranslatorInterface $translator;

    private UserRepository $userRepository;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = $this->client->getContainer()->get(UserRepository::class);
        $this->translator = $this->client->getContainer()->get(TranslatorInterface::class);

        // Dodanie użytkownika z rolą ROLE_ADMIN do bazy danych testowej
        $adminUser = new User();
        $adminUser->setEmail('admin@example.com');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setPassword(
            $this->client->getContainer()->get('security.password_hasher')->hashPassword($adminUser, 'admin1234')
        );
        $this->userRepository->save($adminUser);

        // Dodanie użytkownika z rolą ROLE_USER do bazy danych testowej
        $testUser = new User();
        $testUser->setEmail('user@example.com');
        $testUser->setRoles(['ROLE_USER']);
        $testUser->setPassword(
            $this->client->getContainer()->get('security.password_hasher')->hashPassword($testUser, 'user1234')
        );
        $this->userRepository->save($testUser);
    }

    public function testIndex(): void
    {
        $this->client->loginUser($this->getAdminUser());

        $this->client->request('GET', '/user');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table'); // Sprawdza, czy tabela użytkowników jest renderowana
    }

    public function testShow(): void
    {
        $this->client->loginUser($this->getAdminUser());

        $user = $this->getTestUser();
        $this->client->request('GET', '/user/' . $user->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', "Szczegóły użytkownika"); // Sprawdza, czy email użytkownika jest wyświetlany
    }


    public function testEditPassword(): void
    {
        $this->client->loginUser($this->getAdminUser());

        $user = $this->getTestUser();
        $this->client->request('GET', '/user/' . $user->getId() . '/edit/password');
        $this->assertResponseIsSuccessful();

        $submitLabel = $this->translator->trans('action.edit'); // Pobranie przetłumaczonej etykiety
        $this->client->submitForm($submitLabel, [
            'user_password[password][first]' => 'newpassword123',
            'user_password[password][second]' => 'newpassword123',
        ]);

        $this->assertResponseRedirects('/');
    }

    public function testEditEmail(): void
    {
        $this->client->loginUser($this->getAdminUser());

        $user = $this->getTestUser();
        $this->client->request('GET', '/user/' . $user->getId() . '/edit/email');
        $this->assertResponseIsSuccessful();

        $submitLabel = $this->translator->trans('action.edit'); // Pobranie przetłumaczonej etykiety
        $this->client->submitForm($submitLabel, [
            'user_email[email]' => 'newemail@example.com',
        ]);

        $this->assertResponseRedirects('/');
    }
    private function getAdminUser(): User
    {
        return $this->userRepository->findOneBy(['email' => 'admin@example.com']);
    }

    private function getTestUser(): User
    {
        return $this->userRepository->findOneBy(['email' => 'user@example.com']);
    }
}