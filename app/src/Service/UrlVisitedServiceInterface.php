<?php
/*
 * Url Visited Interface.
 */

namespace App\Service;

use App\Entity\UrlVisited;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface UrlVisitedServiceInterface.
 */
interface UrlVisitedServiceInterface
{
    /**
     * Save url visited.
     *
     * @param UrlVisited $urlVisited UrlVisited entity
     */
    public function save(UrlVisited $urlVisited): void;

    /**
     * Count all visits for url.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated urls
     */
    public function countAllVisitsForUrl(int $page): PaginationInterface;

    /**
     * Delete all visits for url.
     *
     * @param int $id Url id
     */
    public function deleteAllVisitsForUrl(int $id): void;
}
