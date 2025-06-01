<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Service\UrlVisitedServiceInterface;
use Knp\Component\Pager\PaginatorInterface;

class UrlVisitedControllerTest extends WebTestCase
{
    public function testUrlVisitedReturnsStatusCode200(): void
    {
        $client = static::createClient();

        // Get container
        $container = static::getContainer();

        $paginator = $container->get(PaginatorInterface::class);

        $dummyData = [
            (object)[
                'visits' => 42,
                'shortUrl' => 'abc123',
                'longUrl' => 'https://example.com',
            ],
        ];

        $pagination = $paginator->paginate($dummyData, 1, 10);

        $mockService = $this->createMock(UrlVisitedServiceInterface::class);
        $mockService->method('countAllVisitsForUrl')->willReturn($pagination);

        $container->set(UrlVisitedServiceInterface::class, $mockService);

        $client->request('GET', '/popular');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('abc123', $client->getResponse()->getContent());
        $this->assertStringContainsString('https://example.com', $client->getResponse()->getContent());
    }
}
