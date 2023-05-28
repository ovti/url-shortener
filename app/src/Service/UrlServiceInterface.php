<?php

namespace App\Service;

use App\Entity\Url;
use Knp\Component\Pager\Pagination\PaginationInterface;

interface UrlServiceInterface {
    public function getPaginatedList(int $page): PaginationInterface;
    public function save(Url $url): void;
    public function generateShortUrl(string $longUrl): string;

}