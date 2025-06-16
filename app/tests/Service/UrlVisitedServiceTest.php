<?php

/**
 * UrlVisited service test.
 */

namespace App\Tests\Service;

use App\Entity\UrlVisited;
use App\Repository\UrlVisitedRepository;
use App\Service\UrlVisitedService;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class UrlVisitedServiceTest.
 */
class UrlVisitedServiceTest extends TestCase
{
    /**
     * UrlVisited repository mock.
     */
    private UrlVisitedRepository $urlVisitedRepository;

    /**
     * Paginator mock.
     */
    private PaginatorInterface $paginator;

    /**
     * UrlVisited service.
     */
    private UrlVisitedService $service;

    /**
     * Set up test.
     */
    protected function setUp(): void
    {
        $this->urlVisitedRepository = $this->createMock(UrlVisitedRepository::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->service = new UrlVisitedService($this->urlVisitedRepository, $this->paginator);
    }

    /**
     * Test counting all visits for a URL.
     */
    public function testCountAllVisitsForUrl(): void
    {
        // given
        $dummyData = [
            (object) [
                'visits' => 42,
                'shortUrl' => 'abc123',
                'longUrl' => 'https://example.com',
            ],
        ];

        $pagination = new SlidingPagination();
        $pagination->setItems($dummyData);

        $this->urlVisitedRepository->method('countAllVisitsForUrl')->willReturn($dummyData);
        $this->paginator->method('paginate')->willReturn($pagination);

        // when
        $result = $this->service->countAllVisitsForUrl(1);

        // then
        $this->assertEquals($pagination, $result);
    }

    /**
     * Test saving a UrlVisited entity.
     */
    public function testSave(): void
    {
        // given
        $urlVisited = new UrlVisited();

        $this->urlVisitedRepository
            ->expects($this->once())
            ->method('save')
            ->with($urlVisited);

        // when
        $this->service->save($urlVisited);
    }

    /**
     * Test deleting all visits for a URL.
     */
    public function testDeleteAllVisitsForUrl(): void
    {
        // given
        $this->urlVisitedRepository
            ->expects($this->once())
            ->method('deleteAllVisitsForUrl')
            ->with(1);

        // when
        $this->service->deleteAllVisitsForUrl(1);
    }
}
