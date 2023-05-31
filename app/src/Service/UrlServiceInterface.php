<?php
/**
 * Url service.
 */

namespace App\Service;

use App\Entity\Url;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface UrlServiceInterface.
 */
interface UrlServiceInterface
{
    /**
     * Get paginated urls.
     * @param int $page Page number
     */
    public function getPaginatedList(int $page, User $users): PaginationInterface;

    /**
     * Save url.
     * @param Url $url
     */
    public function save(Url $url): void;
    /**
     * Delete url.
     * @param Url $url
     */
    public function delete(Url $url): void;
    /**
     * Generate short url.
     * @return string
     */
    public function generateShortUrl(): string;

}
