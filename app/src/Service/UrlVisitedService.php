<?php
/**
 * Url Visited service.
 */

namespace App\Service;

use App\Entity\UrlVisited;
use App\Repository\UrlVisitedRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class UrlVisitedService.
 */
class UrlVisitedService implements UrlVisitedServiceInterface
{
    /**
     * UrlVisited repository.
     */
    private UrlVisitedRepository $urlVisitedRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * UrlVisitedService constructor.
     *
     * @param UrlVisitedRepository $urlVisitedRepository UrlVisited repository
     * @param PaginatorInterface   $paginator            Paginator
     */
    public function __construct(UrlVisitedRepository $urlVisitedRepository, PaginatorInterface $paginator)
    {
        $this->urlVisitedRepository = $urlVisitedRepository;
        $this->paginator = $paginator;
    }

    /**
     * Save url visited.
     *
     * @param UrlVisited $urlVisited UrlVisited entity
     */
    public function save(UrlVisited $urlVisited): void
    {
        $this->urlVisitedRepository->save($urlVisited);
    }

    /**
     * Count all visits for url.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated urls
     */
    public function countAllVisitsForUrl(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->urlVisitedRepository->countAllVisitsForUrl(),
            $page,
            UrlVisitedRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Delete all visits for url.
     *
     * @param int $id Url id
     */
    public function deleteAllVisitsForUrl(int $id): void
    {
        $this->urlVisitedRepository->deleteAllVisitsForUrl($id);
    }
}
