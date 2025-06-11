<?php

namespace App\Tests\Service;

use App\Entity\UrlVisited;
use App\Repository\UrlVisitedRepository;
use App\Service\UrlVisitedService;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;

class UrlVisitedServiceTest extends TestCase
{
    public function testCountAllVisitsForUrl(): void
    {
        $mockRepository = $this->createMock(UrlVisitedRepository::class);
        $mockPaginator = $this->createMock(PaginatorInterface::class);

        $dummyData = [
            (object)[
                'visits' => 42,
                'shortUrl' => 'abc123',
                'longUrl' => 'https://example.com',
            ],
        ];

        $pagination = new SlidingPagination();
        $pagination->setItems($dummyData);

        $mockRepository->method('countAllVisitsForUrl')->willReturn($dummyData);
        $mockPaginator->method('paginate')->willReturn($pagination);

        $service = new UrlVisitedService($mockRepository, $mockPaginator);

        $result = $service->countAllVisitsForUrl(1);

        $this->assertEquals($pagination, $result);
    }

    public function testSave(): void
    {
        $mockRepository = $this->createMock(UrlVisitedRepository::class);
        $mockPaginator = $this->createMock(PaginatorInterface::class);

        $mockRepository->expects($this->once())->method('save');

        $service = new UrlVisitedService($mockRepository, $mockPaginator);

        $urlVisited = new UrlVisited();
        $service->save($urlVisited);
    }

    public function testDeleteAllVisitsForUrl(): void
    {
        $mockRepository = $this->createMock(UrlVisitedRepository::class);
        $mockPaginator = $this->createMock(PaginatorInterface::class);

        $mockRepository->expects($this->once())->method('deleteAllVisitsForUrl')->with(1);

        $service = new UrlVisitedService($mockRepository, $mockPaginator);

        $service->deleteAllVisitsForUrl(1);
    }
}