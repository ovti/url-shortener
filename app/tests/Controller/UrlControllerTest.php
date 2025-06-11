<?php

namespace App\Tests\Controller;

use App\Entity\Tag;
use App\Entity\Url;
use App\Entity\User;
use App\Repository\TagRepository;
use App\Repository\UrlRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UrlControllerTest extends WebTestCase
{
    private $client;
    private $urlRepository;
    private $userRepository;
    private $tagRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->urlRepository = static::getContainer()->get(UrlRepository::class);
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->tagRepository = static::getContainer()->get(TagRepository::class);
    }

    private function loginAsAdmin(): void
    {
        $admin = $this->userRepository->findOneByEmail('admin0@example.com');
        $this->client->loginUser($admin);
    }

    private function loginAsUser(): void
    {
        $user = $this->userRepository->findOneByEmail('user0@example.com');
        $this->client->loginUser($user);
    }

    public function testIndex(): void
    {
        $this->loginAsUser();

        $this->client->request('GET', '/url');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    public function testList(): void
    {
        $this->client->request('GET', '/url/list');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    public function testShow(): void
    {
        $url = $this->urlRepository->findOneBy(['isBlocked' => false]);
        $this->assertNotNull($url, 'Brak niezablokowanych URL-i w bazie danych');

        $this->client->request('GET', '/url/' . $url->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $url->getLongUrl());
    }

    public function testShowBlockedAsAdmin(): void
    {
        $this->loginAsAdmin();

        $url = $this->urlRepository->findOneBy(['isBlocked' => true]);
        if (!$url) {
            $this->markTestSkipped('Brak zablokowanych URL-i w bazie danych');
        }

        $this->client->request('GET', '/url/' . $url->getId());
        $this->assertResponseIsSuccessful();
    }

    public function testShowBlockedAsUser(): void
    {
        $this->loginAsUser();

        $url = $this->urlRepository->findOneBy(['isBlocked' => true]);
        if (!$url) {
            $this->markTestSkipped('Brak zablokowanych URL-i w bazie danych');
        }

        $this->client->request('GET', '/url/' . $url->getId());
        $this->assertResponseStatusCodeSame(403); // Dostęp zabroniony
    }

    public function testCreate(): void
    {
        $this->loginAsUser();

        $this->client->request('GET', '/url/create');
        $this->assertResponseIsSuccessful();

        $tag = $this->tagRepository->findOneBy([]);
        $this->assertNotNull($tag, 'Brak tagów w bazie danych');

        $this->client->submitForm('Zapisz', [
            'Url[longUrl]' => 'https://test.example.com',
            'Url[tags]' => $tag->getName()
        ]);

        $this->assertResponseRedirects('/url/list');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');

        $url = $this->urlRepository->findOneBy(['longUrl' => 'https://test.example.com']);
        $this->assertNotNull($url);
    }

    public function testEdit(): void
    {
        $this->loginAsUser();

        $user = $this->userRepository->findOneByEmail('user0@example.com');
        $url = $this->urlRepository->findOneBy(['users' => $user]);
        $this->assertNotNull($url, 'Brak URL-i dla testowego użytkownika');

        $urlId = $url->getId();
        $this->client->request('GET', '/url/' . $urlId . '/edit');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Edytuj', [
            'Url[longUrl]' => 'https://updated.example.com',
        ]);

        $this->assertResponseRedirects('/url');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');

        // Pobierz świeżą encję zamiast używania clear()
        $updatedUrl = $this->urlRepository->find($urlId);
        $this->assertEquals('https://updated.example.com', $updatedUrl->getLongUrl());
    }

    public function testBlock(): void
    {
        $this->loginAsAdmin();

        $url = $this->urlRepository->findOneBy(['isBlocked' => false]);
        $this->assertNotNull($url, 'Oczekiwano, że w bazie będzie przynajmniej jeden URL, który nie jest zablokowany');

        $crawler = $this->client->request('GET', '/url/' . $url->getId() . '/block');
        $this->assertResponseIsSuccessful();

        // Ustawienie przyszłej daty jako daty wygaśnięcia blokady
        $futureDate = new \DateTime('tomorrow');

        $form = $crawler->filter('form[name="BlockUrl"]')->form();
        $form['BlockUrl[blockExpiration][date][day]'] = $futureDate->format('j');
        $form['BlockUrl[blockExpiration][date][month]'] = $futureDate->format('n');
        $form['BlockUrl[blockExpiration][date][year]'] = $futureDate->format('Y');
        $form['BlockUrl[blockExpiration][time][hour]'] = $futureDate->format('G');
        $form['BlockUrl[blockExpiration][time][minute]'] = (int)$futureDate->format('i');

        $this->client->submit($form);
        $this->assertResponseRedirects('/url/list');

        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Zablokowano.');
    }

    public function testUnblock(): void
    {
        $this->loginAsAdmin();

        // Przygotuj zablokowany URL do testu
        $url = $this->prepareBlockedUrl($this->urlRepository);
        $this->assertNotNull($url, 'Oczekiwano, że w bazie będzie zablokowany URL');

        $crawler = $this->client->request('GET', '/url/' . $url->getId() . '/unblock');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects('/url/list');

        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Odblokowano.');
    }

    /**
     * Przygotowuje zablokowany URL do testu odblokowania
     */
    private function prepareBlockedUrl(UrlRepository $repository): ?Url
    {
        $url = $repository->findOneBy(['isBlocked' => true]);

        if (!$url) {
            // Jeśli nie ma zablokowanego URL, znajdź dowolny i go zablokuj
            $url = $repository->findOneBy([]);
            if ($url) {
                $url->setIsBlocked(true);
                $url->setBlockExpiration(new \DateTime('+1 day'));
                $repository->save($url);
            }
        }

        return $url;
    }

    public function testDelete(): void
    {
        $this->loginAsUser();

        $user = $this->userRepository->findOneByEmail('user0@example.com');
        $url = $this->urlRepository->findOneBy(['users' => $user]);
        $this->assertNotNull($url, 'Brak URL-i dla testowego użytkownika');

        $this->client->request('GET', '/url/' . $url->getId() . '/delete');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Usuń');

        $this->assertResponseRedirects('/url');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');

        $deletedUrl = $this->urlRepository->find($url->getId());
        $this->assertNull($deletedUrl);
    }
}