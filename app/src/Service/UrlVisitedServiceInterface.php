<?php
/*
 * Url Visited Interface.
 */

namespace App\Service;

use App\Entity\UrlVisited;

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
}
