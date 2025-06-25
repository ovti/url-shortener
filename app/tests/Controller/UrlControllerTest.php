<?php

/**
 * Url controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Tag;
use App\Entity\Url;
use App\Entity\User;
use App\Repository\TagRepository;
use App\Repository\UrlRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UrlControllerTest.
 */
class UrlControllerTest extends WebTestCase
{
    /**
     * Test client.
     */
    private KernelBrowser $client;

    /**
     * URL repository.
     */
    private UrlRepository $urlRepository;

    /**
     * User repository.
     */
    private UserRepository $userRepository;

    /**
     * Tag repository.
     */
    private TagRepository $tagRepository;

    /**
     * Entity manager.
     */
    private EntityManagerInterface $em;

    /**
     * Password hasher.
     */
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * Admin user.
     */
    private User $admin;

    /**
     * Regular user.
     */
    private User $regularUser;

    /**
     * Second regular user.
     */
    private User $secondUser;

    /**
     * Tag entity.
     */
    private Tag $tag;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->urlRepository = $container->get(UrlRepository::class);
        $this->userRepository = $container->get(UserRepository::class);
        $this->tagRepository = $container->get(TagRepository::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $this->createTestData();
    }

    /**
     * Test index page for URLs.
     */
    public function testShow(): void
    {
        $url = $this->createUrlForUser($this->regularUser);
        $this->client->request('GET', '/url/'.$url->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $url->getLongUrl());
    }

    /**
     * Test setting and getting create time.
     */
    public function testSetAndGetCreateTime(): void
    {
        $url = new Url();
        $date = new \DateTimeImmutable('2025-01-01 12:00:00');

        $url->setCreateTime($date);
        $this->assertEquals($date, $url->getCreateTime());

        $url->setCreateTime(null);
        $this->assertNull($url->getCreateTime());
    }

    /**
     * Test removing a tag from URL.
     */
    public function testRemoveTag(): void
    {
        $url = new Url();
        $tag = new Tag();
        $tag->setName('test-tag-remove');

        $this->em->persist($tag);
        $this->em->flush();

        $url->addTag($tag);
        $this->assertEquals(1, $url->getTags()->count());
        $this->assertTrue($url->getTags()->contains($tag));

        $url->removeTag($tag);
        $this->assertEquals(0, $url->getTags()->count());
        $this->assertFalse($url->getTags()->contains($tag));
    }

    /**
     * Test index page for URLs as admin.
     */
    public function testShowBlockedAsAdmin(): void
    {
        $this->loginAsAdmin();
        $url = $this->createBlockedUrl();
        $this->client->request('GET', '/url/'.$url->getId());
        $this->assertResponseIsSuccessful();
    }

    /**
     * Test showing a blocked URL as a regular user.
     */
    public function testShowBlockedAsUser(): void
    {
        $this->loginAsUser();
        $url = $this->createBlockedUrl();
        $this->client->request('GET', '/url/'.$url->getId());
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Test creating a new URL.
     */
    public function testCreate(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/url/create');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Zapisz', [
            'Url[longUrl]' => 'https://test.example.com',
            'Url[tags]' => $this->tag->getName(),
        ]);

        $this->assertResponseRedirects('/url/list');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }

    /**
     * Test editing an existing URL.
     */
    public function testEdit(): void
    {
        $this->loginAsUser();
        $url = $this->createUrlForUser($this->regularUser);

        $this->client->request('GET', '/url/'.$url->getId().'/edit');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Edytuj', [
            'Url[longUrl]' => 'https://updated.example.com',
        ]);

        $this->assertResponseRedirects('/url');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }

    /**
     * Test blocking a URL.
     */
    public function testBlock(): void
    {
        $this->loginAsAdmin();
        $url = $this->createUrlForUser($this->regularUser);

        $crawler = $this->client->request('GET', '/url/'.$url->getId().'/block');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="BlockUrl"]')->form();

        $tomorrow = new \DateTime('tomorrow');

        $form['BlockUrl[blockExpiration][date][day]']->setValue($tomorrow->format('d'));
        $form['BlockUrl[blockExpiration][date][month]']->setValue($tomorrow->format('n'));
        $form['BlockUrl[blockExpiration][date][year]']->setValue($tomorrow->format('Y'));
        $form['BlockUrl[blockExpiration][time][hour]']->setValue($tomorrow->format('G'));
        $form['BlockUrl[blockExpiration][time][minute]']->setValue('0');

        $this->client->submit($form);

        $this->assertResponseRedirects('/url/list');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Zablokowano.');

        $updatedUrl = $this->urlRepository->find($url->getId());
        $this->assertTrue($updatedUrl->isIsBlocked());
    }

    /**
     * Test unblocking a URL.
     */
    public function testUnblock(): void
    {
        $this->loginAsAdmin();
        $url = $this->createBlockedUrl();

        $crawler = $this->client->request('GET', '/url/'.$url->getId().'/unblock');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects('/url/list');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Odblokowano.');
    }

    /**
     * Test deleting a URL.
     */
    public function testDelete(): void
    {
        $this->loginAsUser();
        $url = $this->createUrlForUser($this->regularUser);

        $this->client->request('GET', '/url/'.$url->getId().'/delete');
        $this->assertResponseIsSuccessful();
        $this->client->submitForm('Usuń');

        $this->assertResponseRedirects('/url');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');

        $this->assertNull($this->urlRepository->find($url->getId()));
    }

    /**
     * Test editing a URL as a forbidden user.
     */
    public function testEditAsForbiddenUser(): void
    {
        $this->loginAsUser();
        $url = $this->createUrlForUser($this->secondUser);

        $this->client->request('GET', '/url/'.$url->getId().'/edit');
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Test blocking an expired URL.
     */
    public function testUnblockExpiredBlock(): void
    {
        $url = $this->createUrlForUser($this->admin, true);
        $url->setBlockExpiration(new \DateTimeImmutable('-1 day'));
        $this->em->flush();

        $this->loginAsAdmin();
        $this->client->request('GET', '/url/'.$url->getId().'/unblock');
        $this->assertResponseRedirects('/url/list');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Odblokowano');
    }

    /**
     * Test index page with tag filter.
     */
    public function testIndexWithTagFilter(): void
    {
        $this->loginAsUser();
        $url = $this->createUrlForUser($this->regularUser);
        $url->addTag($this->tag);
        $this->urlRepository->save($url, true);

        $this->client->request('GET', '/url?filters_tag_id='.$this->tag->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table tbody tr');
        $this->assertSelectorTextContains('body', $url->getLongUrl());
    }

    /**
     * Test listing URLs with tag filter.
     */
    public function testListWithTagFilter(): void
    {
        $url = $this->createUrlForUser($this->regularUser);
        $url->addTag($this->tag);
        $this->urlRepository->save($url, true);

        $this->client->request('GET', '/url/list?filters_tag_id='.$this->tag->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table tbody tr');
    }

    /**
     * Test creating a URL as a guest user.
     */
    public function testCreateAsGuest(): void
    {
        $this->client->request('GET', '/url/create');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Zapisz', [
            'Url[email]' => 'guest_test@example.com',
            'Url[longUrl]' => 'https://guest.example.com',
            'Url[tags]' => $this->tag->getName(),
        ]);

        $this->assertResponseRedirects('/url/list');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');

        $url = $this->urlRepository->findOneBy(['longUrl' => 'https://guest.example.com']);
        $this->assertNotNull($url);
        $this->assertEquals('guest_test@example.com', $url->getGuestUser()->getEmail());
    }

    /**
     * Test deleting a URL via the DELETE method.
     */
    public function testDeleteViaDeleteMethod(): void
    {
        $this->loginAsUser();
        $url = $this->createUrlForUser($this->regularUser);

        $this->client->request('DELETE', '/url/'.$url->getId().'/delete');

        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * Test editing a blocked URL as a user.
     */
    public function testEditBlockedAsUser(): void
    {
        $this->loginAsUser();
        $url = $this->createUrlForUser($this->regularUser);
        $url->setIsBlocked(true);
        $this->em->flush();

        $this->client->request('GET', '/url/'.$url->getId().'/edit');
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Test deleting a URL as a forbidden user.
     */
    public function testDeleteAsForbiddenUser(): void
    {
        $this->loginAsUser();
        $url = $this->createUrlForUser($this->secondUser);
        $this->client->request('GET', '/url/'.$url->getId().'/delete');
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Create test data for the tests.
     */
    private function createTestData(): void
    {
        $this->admin = new User();
        $this->admin->setEmail('admin0@example.com');
        $this->admin->setRoles(['ROLE_ADMIN']);
        $this->admin->setPassword($this->passwordHasher->hashPassword($this->admin, 'test'));

        $this->regularUser = new User();
        $this->regularUser->setEmail('user0@example.com');
        $this->regularUser->setRoles(['ROLE_USER']);
        $this->regularUser->setPassword($this->passwordHasher->hashPassword($this->regularUser, 'test'));

        $this->secondUser = new User();
        $this->secondUser->setEmail('user1@example.com');
        $this->secondUser->setRoles(['ROLE_USER']);
        $this->secondUser->setPassword($this->passwordHasher->hashPassword($this->secondUser, 'test'));

        $this->tag = new Tag();
        $this->tag->setName('test-tag');

        $this->em->persist($this->admin);
        $this->em->persist($this->regularUser);
        $this->em->persist($this->secondUser);
        $this->em->persist($this->tag);
        $this->em->flush();
    }

    /**
     * Log in as admin user.
     */
    private function loginAsAdmin(): void
    {
        $this->client->loginUser($this->admin);
    }

    /**
     * Log in as a regular user.
     */
    private function loginAsUser(): void
    {
        $this->client->loginUser($this->regularUser);
    }

    /**
     * Create a URL for a specific user.
     *
     * @param User $user    User entity
     * @param bool $blocked Whether the URL should be blocked
     *
     * @return Url Created URL entity
     */
    private function createUrlForUser(User $user, bool $blocked = false): Url
    {
        $url = new Url();
        $url->setLongUrl('https://example.com/'.uniqid());
        $url->setIsBlocked($blocked);
        $url->setUsers($user);
        $url->setShortUrl(substr(md5(uniqid()), 0, 8));

        $this->em->persist($url);
        $this->em->flush();

        return $url;
    }

    /**
     * Create a blocked URL for the admin user.
     *
     * @return Url Created blocked URL entity
     */
    private function createBlockedUrl(): Url
    {
        $url = $this->createUrlForUser($this->admin, true);
        $url->setBlockExpiration(new \DateTimeImmutable('+1 day'));
        $this->em->flush();

        return $url;
    }
}
