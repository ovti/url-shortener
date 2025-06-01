<?php

namespace App\Tests\Controller;

use App\Controller\UrlController;
use App\Entity\User;
use App\Service\UrlServiceInterface;
use App\Service\UrlVisitedServiceInterface;
use App\Service\GuestUserServiceInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class UrlControllerTest extends TestCase
{
    public function testIndexReturnsResponse()
    {
        $urlServiceMock = $this->createMock(UrlServiceInterface::class);
        $translatorMock = $this->createMock(TranslatorInterface::class);
        $urlVisitedServiceMock = $this->createMock(UrlVisitedServiceInterface::class);
        $requestStackMock = $this->createMock(RequestStack::class);
        $guestUserServiceMock = $this->createMock(GuestUserServiceInterface::class);

        $paginationMock = $this->createMock(PaginationInterface::class);

        $request = new Request(['page' => 1]);

        $user = $this->createMock(User::class);

        $urlServiceMock->expects($this->once())
            ->method('getPaginatedList')
            ->with(1, $user, ['tag_id' => 0])
            ->willReturn($paginationMock);

        $controller = $this->getMockBuilder(UrlController::class)
            ->setConstructorArgs([
                $urlServiceMock,
                $translatorMock,
                $urlVisitedServiceMock,
                $requestStackMock,
                $guestUserServiceMock,
            ])
            ->onlyMethods(['getUser', 'render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $responseMock = $this->createMock(Response::class);

        $controller->expects($this->once())
            ->method('render')
            ->with('url/index.html.twig', ['pagination' => $paginationMock])
            ->willReturn($responseMock);

        $response = $controller->index($request);

        $this->assertSame($responseMock, $response);
    }
}
