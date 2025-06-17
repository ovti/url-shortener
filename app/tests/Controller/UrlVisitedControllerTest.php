<?php

/**
 * UrlVisited controller test.
 */

namespace App\Tests\Controller;

use App\Entity\UrlVisited;
use App\Repository\UrlVisitedRepository;
use App\Service\UrlVisitedServiceInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * Test entity method getId.
     */
    public function testUrlVisitedGetId(): void
    {
        // given
        $urlVisited = new UrlVisited();
        $reflection = new \ReflectionClass(UrlVisited::class);
        $property = $reflection->getProperty('id');
        $property->setValue($urlVisited, 1);

        // when
        $result = $urlVisited->getId();

        // then
        $this->assertEquals(1, $result);
    }

    /**
     * Test repository method save.
     */
    public function testSave(): void
    {
        // given
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->once())->method('flush');

        $urlVisitedRepository = $this->getMockBuilder(UrlVisitedRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEntityManager'])
            ->getMock();
        $urlVisitedRepository->method('getEntityManager')->willReturn($entityManager);

        $reflection = new \ReflectionClass($urlVisitedRepository);
        $property = $reflection->getProperty('_em');
        $property->setValue($urlVisitedRepository, $entityManager);

        $urlVisited = new UrlVisited();

        // when
        $urlVisitedRepository->save($urlVisited);
    }

    /**
     * Test repository method getOrCreateQueryBuilder.
     */
    public function testGetOrCreateQueryBuilder(): void
    {
        // given
        $mockQueryBuilder = $this->createMock(QueryBuilder::class);
        $urlVisitedRepository = $this->getMockBuilder(UrlVisitedRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();
        $urlVisitedRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('urlVisited')
            ->willReturn($mockQueryBuilder);

        // when
        $reflectionMethod = new \ReflectionMethod(
            UrlVisitedRepository::class,
            'getOrCreateQueryBuilder'
        );
        $result = $reflectionMethod->invoke($urlVisitedRepository);

        // then
        $this->assertSame($mockQueryBuilder, $result);
    }

    /**
     * Test repository method getOrCreateQueryBuilder with provided query builder.
     */
    public function testGetOrCreateQueryBuilderWithProvidedBuilder(): void
    {
        // given
        $mockQueryBuilder = $this->createMock(QueryBuilder::class);
        $urlVisitedRepository = $this->getMockBuilder(UrlVisitedRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        // when
        $reflectionMethod = new \ReflectionMethod(
            UrlVisitedRepository::class,
            'getOrCreateQueryBuilder'
        );
        $result = $reflectionMethod->invoke($urlVisitedRepository, $mockQueryBuilder);

        // then
        $this->assertSame($mockQueryBuilder, $result);
    }

    /**
     * Test repository method countAllVisitsForUrl.
     */
    public function testCountAllVisitsForUrl(): void
    {
        // given
        $expectedResults = [
            [
                'visits' => 10,
                'longUrl' => 'https://example.com',
                'shortUrl' => 'abc123',
                'latestVisitTime' => new \DateTime(),
            ],
            [
                'visits' => 5,
                'longUrl' => 'https://test.com',
                'shortUrl' => 'def456',
                'latestVisitTime' => new \DateTime(),
            ],
        ];

        $mockQuery = $this->createMock(AbstractQuery::class);
        $mockQuery->method('getResult')->willReturn($expectedResults);

        $mockQueryBuilder = $this->createMock(QueryBuilder::class);
        $mockQueryBuilder->method('select')->willReturnSelf();
        $mockQueryBuilder->method('leftJoin')->willReturnSelf();
        $mockQueryBuilder->method('groupBy')->willReturnSelf();
        $mockQueryBuilder->method('orderBy')->willReturnSelf();
        $mockQueryBuilder->method('addOrderBy')->willReturnSelf();
        $mockQueryBuilder->method('getQuery')->willReturn($mockQuery);

        $urlVisitedRepository = $this->getMockBuilder(UrlVisitedRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();
        $urlVisitedRepository->method('createQueryBuilder')
            ->with('urlVisited')
            ->willReturn($mockQueryBuilder);

        // when
        $result = $urlVisitedRepository->countAllVisitsForUrl();

        // then
        $this->assertSame($expectedResults, $result);
    }

    /**
     * Test entity methods getVisitTime and setVisitTime.
     */
    public function testUrlVisitedVisitTime(): void
    {
        // given
        $urlVisited = new UrlVisited();
        $visitTime = new \DateTimeImmutable('2025-01-01');

        // when
        $urlVisited->setVisitTime($visitTime);
        $result = $urlVisited->getVisitTime();

        // then
        $this->assertEquals($visitTime, $result);
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
