<?php

/**
 * Tag controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Tag;
use App\Entity\User;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class TagControllerTest.
 */
class TagControllerTest extends WebTestCase
{
    /**
     * Test client.
     */
    private KernelBrowser $httpClient;

    /**
     * Entity manager.
     */
    private EntityManagerInterface $entityManager;

    /**
     * Set up tests.
     */
    protected function setUp(): void
    {
        $this->httpClient = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->loginAsAdmin($this->httpClient);
    }

    /**
     * Test show tag page.
     */
    public function testShowTag(): void
    {
        // given
        $tag = $this->createTag('ShowTag');

        // when
        $this->httpClient->request('GET', '/tag/'.$tag->getId());

        // then
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $tag->getName());
    }

    /**
     * Test creating new tag.
     */
    public function testCreateNewTag(): void
    {
        // when
        $crawler = $this->httpClient->request('GET', '/tag/create');
        $this->assertResponseIsSuccessful();

        // given
        $form = $crawler->filter('form')->form([
            'tag[name]' => 'TestTag',
        ]);
        $this->httpClient->submit($form);

        // then
        $this->assertResponseRedirects('/tag');
        $this->httpClient->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Udało się utworzyć.');

        $tag = static::getContainer()->get(TagRepository::class)->findOneBy(['name' => 'TestTag']);
        $this->assertNotNull($tag, 'Oczekiwano, że “TestTag” zostanie zapisany w bazie.');
    }

    /**
     * Test editing tag.
     */
    public function testEditTag(): void
    {
        // given
        $tag = $this->createTag('ToEdit');

        // when
        $crawler = $this->httpClient->request('GET', '/tag/'.$tag->getId().'/edit');
        $this->assertResponseIsSuccessful();
        $form = $crawler->filter('form')->form([
            'tag[name]' => 'UpdatedTag',
        ]);
        $this->httpClient->submit($form);

        // then
        $this->assertResponseRedirects('/tag');
        $this->httpClient->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Zaktualizowano.');

        $updatedTag = $this->entityManager->getRepository(Tag::class)->findOneBy(['name' => 'UpdatedTag']);
        $this->assertNotNull($updatedTag, 'Oczekiwano, że “UpdatedTag” zostanie zapisany w bazie.');
    }

    /**
     * Test deleting tag.
     */
    public function testDeleteTag(): void
    {
        // given
        $tag = $this->createTag('ToDelete');

        // when
        $crawler = $this->httpClient->request('GET', '/tag/'.$tag->getId().'/delete');
        $this->assertResponseIsSuccessful();
        $form = $crawler->filter('form')->form();
        $this->httpClient->submit($form);

        // then
        $this->assertResponseRedirects('/tag');
        $this->httpClient->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Udało się usunąć.');

        $deletedTag = $this->entityManager->getRepository(Tag::class)->find($tag->getId());
        $this->assertNull($deletedTag, 'Oczekiwano, że Tag zostanie usunięty z bazy.');
    }

    /**
     * Log in as admin.
     *
     * @param KernelBrowser $client HTTP client
     */
    private function loginAsAdmin(KernelBrowser $client): void
    {
        $container = static::getContainer();
        $hasher = $container->get(UserPasswordHasherInterface::class);
        $admin = $this->entityManager->getRepository(User::class)->findOneByEmail('admin0@example.com');

        if (!$admin) {
            $admin = $this->createAdminUser($hasher);
        }

        $client->loginUser($admin);
    }

    /**
     * Create admin user.
     *
     * @param UserPasswordHasherInterface $hasher Password hasher
     *
     * @return User Admin user entity
     */
    private function createAdminUser(UserPasswordHasherInterface $hasher): User
    {
        $user = new User();
        $user->setEmail('admin0@example.com');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($hasher->hashPassword($user, 'test123'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * Create tag.
     *
     * @param string $name Tag name
     *
     * @return Tag Tag entity
     */
    private function createTag(string $name = 'InitialTag'): Tag
    {
        $tag = new Tag();
        $tag->setName($name);

        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        return $tag;
    }
}
