<?php

/**
 * Url redirect controller test.
 */

namespace App\Tests\Controller;

use App\Controller\UrlRedirectController;
use App\Entity\Url;
use App\Entity\UrlVisited;
use App\Service\UrlServiceInterface;
use App\Service\UrlVisitedService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UrlRedirectControllerTest.
 */
class UrlRedirectControllerTest extends TestCase
{
    private UrlServiceInterface $urlServiceMock;
    private TranslatorInterface $translatorMock;
    private UrlVisitedService $urlVisitedServiceMock;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->urlServiceMock = $this->createMock(UrlServiceInterface::class);
        $this->translatorMock = $this->createMock(TranslatorInterface::class);
        $this->urlVisitedServiceMock = $this->createMock(UrlVisitedService::class);

        $this->request = new Request();
    }

    /**
     * Test index method with valid short URL.
     */
    public function testIndexThrowsNotFoundExceptionWhenUrlNotFound(): void
    {
        $this->urlServiceMock
            ->expects($this->once())
            ->method('findOneByShortUrl')
            ->with('nonexistent')
            ->willReturn(null);

        $this->translatorMock
            ->expects($this->once())
            ->method('trans')
            ->with('message.url_not_found')
            ->willReturn('URL nie znaleziony');

        $controller = $this->getMockBuilder(UrlRedirectController::class)
            ->setConstructorArgs([
                $this->urlServiceMock,
                $this->translatorMock,
                $this->urlVisitedServiceMock,
            ])
            ->onlyMethods(['isGranted', 'addFlash', 'redirectToRoute'])
            ->getMock();

        $controller->expects($this->never())->method('isGranted');
        $controller->expects($this->never())->method('addFlash');
        $controller->expects($this->never())->method('redirectToRoute');

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('URL nie znaleziony');

        $controller->index('nonexistent');
    }

    /**
     * Test index method with a blocked URL.
     */
    public function testIndexRedirectsToLongUrlWhenNotBlocked(): void
    {
        $urlEntityMock = $this->createConfiguredMock(Url::class, [
            'isIsBlocked' => false,
            'getLongUrl'   => 'https://example.com/full',
        ]);

        $this->urlServiceMock
            ->expects($this->once())
            ->method('findOneByShortUrl')
            ->with('short123')
            ->willReturn($urlEntityMock);

        $this->urlVisitedServiceMock
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($visit) use ($urlEntityMock) {
                return $visit instanceof UrlVisited && $visit->getUrl() === $urlEntityMock;
            }));

        $controller = $this->getMockBuilder(UrlRedirectController::class)
            ->setConstructorArgs([
                $this->urlServiceMock,
                $this->translatorMock,
                $this->urlVisitedServiceMock,
            ])
            ->onlyMethods(['isGranted', 'addFlash', 'redirectToRoute'])
            ->getMock();

        $controller->expects($this->never())->method('isGranted');
        $controller->expects($this->never())->method('addFlash');
        $controller->expects($this->never())->method('redirectToRoute');

        $response = $controller->index('short123');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('https://example.com/full', $response->getTargetUrl());
    }

    /**
     * Test index method with a blocked URL that is not expired.
     */
    public function testIndexUnblocksExpiredAndRedirects(): void
    {
        $yesterday = (new \DateTimeImmutable())->modify('-1 day');

        $urlEntityMock = $this->getMockBuilder(Url::class)
            ->onlyMethods(['isIsBlocked', 'getBlockExpiration', 'setIsBlocked', 'setBlockExpiration', 'getLongUrl'])
            ->getMock();

        $urlEntityMock->method('isIsBlocked')->willReturn(true);
        $urlEntityMock->method('getBlockExpiration')->willReturn($yesterday);
        $urlEntityMock->method('getLongUrl')->willReturn('https://example.com/unblocked');

        $urlEntityMock->expects($this->once())->method('setIsBlocked')->with(false);
        $urlEntityMock->expects($this->once())->method('setBlockExpiration')->with(null);

        $this->urlServiceMock
            ->expects($this->once())
            ->method('findOneByShortUrl')
            ->with('expiredBlock')
            ->willReturn($urlEntityMock);

        $this->urlServiceMock
            ->expects($this->once())
            ->method('save')
            ->with($urlEntityMock);

        $controller = new UrlRedirectController($this->urlServiceMock, $this->translatorMock, $this->urlVisitedServiceMock);

        $response = $controller->index('expiredBlock');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('https://example.com/unblocked', $response->getTargetUrl());
    }

    /**
     * Test index method with a blocked URL that is not expired for non-admin users.
     */
    public function testIndexBlockedNotExpiredNonAdmin(): void
    {
        $tomorrow = (new \DateTimeImmutable())->modify('+1 day');

        $urlEntityMock = $this->getMockBuilder(Url::class)
            ->onlyMethods(['isIsBlocked', 'getBlockExpiration', 'getLongUrl'])
            ->getMock();

        $urlEntityMock->expects($this->once())->method('isIsBlocked')->willReturn(true);
        $urlEntityMock->expects($this->once())->method('getBlockExpiration')->willReturn($tomorrow);

        $urlEntityMock->expects($this->never())->method('getLongUrl');

        $this->urlServiceMock
            ->expects($this->once())
            ->method('findOneByShortUrl')
            ->with('blockedFuture')
            ->willReturn($urlEntityMock);

        $controller = $this->getMockBuilder(UrlRedirectController::class)
            ->setConstructorArgs([
                $this->urlServiceMock,
                $this->translatorMock,
                $this->urlVisitedServiceMock,
            ])
            ->onlyMethods(['isGranted', 'addFlash', 'redirectToRoute'])
            ->getMock();

        $controller->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(false);

        $this->translatorMock
            ->expects($this->once())
            ->method('trans')
            ->with('message.blocked_url')
            ->willReturn('URL zablokowany');

        $controller->expects($this->once())
            ->method('addFlash')
            ->with('warning', 'URL zablokowany');

        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('url_list')
            ->willReturn(new RedirectResponse('/tag_list'));

        $this->urlVisitedServiceMock->expects($this->never())->method('save');
        $this->urlServiceMock->expects($this->never())->method('save');

        $response = $controller->index('blockedFuture');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/tag_list', $response->getTargetUrl());
    }

    /**
     * Test index method with a blocked URL that is not expired for admin users.
     */
    public function testIndexBlockedNotExpiredAdmin(): void
    {
        $tomorrow = (new \DateTimeImmutable())->modify('+1 day');

        $urlEntityMock = $this->getMockBuilder(Url::class)
            ->onlyMethods(['isIsBlocked', 'getBlockExpiration', 'getLongUrl'])
            ->getMock();

        $urlEntityMock->method('isIsBlocked')->willReturn(true);
        $urlEntityMock->method('getBlockExpiration')->willReturn($tomorrow);
        $urlEntityMock->method('getLongUrl')->willReturn('https://admin-redirect.com');

        $this->urlServiceMock
            ->expects($this->once())
            ->method('findOneByShortUrl')
            ->with('blockedFutureAdmin')
            ->willReturn($urlEntityMock);

        $controller = $this->getMockBuilder(UrlRedirectController::class)
            ->setConstructorArgs([$this->urlServiceMock, $this->translatorMock, $this->urlVisitedServiceMock])
            ->onlyMethods(['isGranted', 'addFlash', 'redirectToRoute'])
            ->getMock();

        $controller->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(true);

        $this->translatorMock
            ->expects($this->once())
            ->method('trans')
            ->with('message.blocked_url')
            ->willReturn('URL zablokowany');

        $controller->expects($this->once())
            ->method('addFlash')
            ->with('warning', 'URL zablokowany');

        $response = $controller->index('blockedFutureAdmin');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('https://admin-redirect.com', $response->getTargetUrl());
    }
}
