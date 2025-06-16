<?php

/**
 * UrlService tests.
 */

namespace App\Tests\Service;

use App\Entity\Url;
use App\Repository\GuestUserRepository;
use App\Repository\UrlRepository;
use App\Service\TagServiceInterface;
use App\Service\UrlService;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * Class UrlServiceTest.
 */
class UrlServiceTest extends TestCase
{
    private PaginatorInterface $paginator;

    private TagServiceInterface $tagService;

    private UrlRepository $urlRepository;

    private Security $security;

    private GuestUserRepository $guestUserRepository;

    private RequestStack $requestStack;

    private UrlService $urlService;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->tagService = $this->createMock(TagServiceInterface::class);
        $this->urlRepository = $this->createMock(UrlRepository::class);
        $this->security = $this->createMock(Security::class);
        $this->guestUserRepository = $this->createMock(GuestUserRepository::class);
        $this->requestStack = $this->createMock(RequestStack::class);

        $this->urlService = new UrlService(
            $this->paginator,
            $this->tagService,
            $this->urlRepository,
            $this->security,
            $this->guestUserRepository,
            $this->requestStack
        );
    }

    /**
     * Test findOneByShortUrl when URL doesn't exist.
     */
    public function testFindOneByShortUrlWhenNotFound(): void
    {
        $shortUrl = 'abc123';

        $this->urlRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['shortUrl' => $shortUrl])
            ->willReturn(null);

        $result = $this->urlService->findOneByShortUrl($shortUrl);

        $this->assertNull($result);
    }

    /**
     * Test generateShortUrl with collision.
     */
    public function testGenerateShortUrlWithCollision(): void
    {
        $existingUrl = $this->createMock(Url::class);

        $this->urlRepository->expects(self::exactly(2))
            ->method('findOneBy')
            ->willReturnOnConsecutiveCalls($existingUrl, null);

        $result = $this->urlService->generateShortUrl();

        $this->assertIsString($result);
        $this->assertEquals(6, strlen($result));
    }
}
