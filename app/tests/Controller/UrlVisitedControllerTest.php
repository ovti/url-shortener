<?php

/**
 * UrlVisited controller test.
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Service\UrlVisitedServiceInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class UrlVisitedControllerTest.
 */
class UrlVisitedControllerTest extends WebTestCase
{
    /**
     * Test client.
     */
    private KernelBrowser $httpClient;

    /**
     * Service container.
     */
    private ContainerInterface $container;

    /**
     * Set up test.
     */
    protected function setUp(): void
    {
        $this->httpClient = static::createClient();
        $this->container = static::getContainer();
    }

    /**
     * Test that the /popular page returns HTTP 200 status code and displays expected data.
     */
    public function testUrlVisitedReturnsStatusCode200(): void
    {
        // given
        $paginator = $this->container->get(PaginatorInterface::class);

        $dummyData = [
            (object) [
                'visits' => 42,
                'shortUrl' => 'abc123',
                'longUrl' => 'https://example.com',
            ],
        ];

        $pagination = $paginator->paginate($dummyData, 1, 10);

        $mockService = $this->createMock(UrlVisitedServiceInterface::class);
        $mockService->method('countAllVisitsForUrl')->willReturn($pagination);

        $this->container->set(UrlVisitedServiceInterface::class, $mockService);

        // when
        $this->httpClient->request('GET', '/popular');

        // then
        $this->assertEquals(200, $this->httpClient->getResponse()->getStatusCode());
        $this->assertStringContainsString('abc123', $this->httpClient->getResponse()->getContent());
        $this->assertStringContainsString('https://example.com', $this->httpClient->getResponse()->getContent());
    }
}
