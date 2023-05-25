<?php

namespace App\Service;

use App\Entity\Url;
use Knp\Component\Pager\Pagination\PaginationInterface;

interface UrlServiceInterface {
  public function getPaginatedList(int $page): PaginationInterface;
}