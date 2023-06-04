<?php
/*
 * Url Visited Interface.
 */

namespace App\Service;

use App\Entity\UrlVisited;
use App\Repository\UrlVisitedRepository;


/**
 * Interface UrlVisitedServiceInterface.
 */
interface UrlVisitedServiceInterface
{
    /**
     * Save url visited.
     *
     * @param \App\Entity\UrlVisited $urlVisited UrlVisited entity
     *
     * @return void
     */
    public function save(UrlVisited $urlVisited): void;

}