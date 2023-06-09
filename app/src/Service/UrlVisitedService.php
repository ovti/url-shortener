<?php
/**
 * Url Visited service.
 */

namespace App\Service;

use App\Entity\UrlVisited;
use App\Repository\UrlVisitedRepository;

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
     * UrlVisitedService constructor.
     *
     * @param UrlVisitedRepository $urlVisitedRepository UrlVisited repository
     */
    public function __construct(UrlVisitedRepository $urlVisitedRepository)
    {
        $this->urlVisitedRepository = $urlVisitedRepository;
    }

    /**
     * Save url visited.
     *
     * @param \App\Entity\UrlVisited $urlVisited UrlVisited entity
     */
    public function save(UrlVisited $urlVisited): void
    {
        $this->urlVisitedRepository->save($urlVisited);
    }
}
