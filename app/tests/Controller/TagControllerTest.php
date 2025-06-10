<?php

namespace App\Tests\Controller;

use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TagControllerTest extends WebTestCase
{
    private function loginAsAdmin($client): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $admin = $userRepository->findOneByEmail('admin0@example.com');

        if (!$admin) {
            throw new \RuntimeException(
                'Nie znaleziono użytkownika admin0@example.com w bazie. ' .
                'Upewnij się, że fixtures zostały załadowane.'
            );
        }

        $client->loginUser($admin);
    }

    public function testIndexAsAdmin(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/tag');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    public function testShowTag(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $tagRepository = static::getContainer()->get(TagRepository::class);
        $tag = $tagRepository->findOneBy([]);

        $this->assertNotNull(
            $tag,
            'Oczekiwano, że w bazie będzie przynajmniej jeden Tag – ' .
            'dodaj fixture z Tagiem przed uruchomieniem testu.'
        );

        $crawler = $client->request('GET', '/tag/' . $tag->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $tag->getName());
    }

    public function testCreateNewTag(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/tag/create');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form();
        $form['tag[name]'] = 'TestTag';

        $client->submit($form);
        $this->assertResponseRedirects('/tag');

        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Udało się utworzyć.');

        $tagRepository = static::getContainer()->get(TagRepository::class);
        $tag = $tagRepository->findOneBy(['name' => 'TestTag']);
        $this->assertNotNull($tag, 'Oczekiwano, że “TestTag” zostanie zapisany w bazie.');
    }

    public function testEditTag(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $tagRepository = static::getContainer()->get(TagRepository::class);
        $tag = $tagRepository->findOneBy([]);

        $this->assertNotNull(
            $tag,
            'Oczekiwano, że w bazie będzie przynajmniej jeden Tag – ' .
            'dodaj fixture z Tagiem przed uruchomieniem testu.'
        );

        $crawler = $client->request('GET', '/tag/' . $tag->getId() . '/edit');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form();
        $form['tag[name]'] = 'UpdatedTag';

        $client->submit($form);
        $this->assertResponseRedirects('/tag');

        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Zaktualizowano.');

        $updatedTag = $tagRepository->findOneBy(['name' => 'UpdatedTag']);
        $this->assertNotNull($updatedTag, 'Oczekiwano, że “UpdatedTag” zostanie zapisany w bazie.');
    }

    public function testDeleteTag(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $tagRepository = static::getContainer()->get(TagRepository::class);
        $tag = $tagRepository->findOneBy([]);

        $this->assertNotNull(
            $tag,
            'Oczekiwano, że w bazie będzie przynajmniej jeden Tag – ' .
            'dodaj fixture z Tagiem przed uruchomieniem testu.'
        );

        $crawler = $client->request('GET', '/tag/' . $tag->getId() . '/delete');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form();
        $client->submit($form);
        $this->assertResponseRedirects('/tag');

        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Udało się usunąć.');

        $deletedTag = $tagRepository->find($tag->getId());
        $this->assertNull($deletedTag, 'Oczekiwano, że Tag zostanie usunięty z bazy.');
    }
}