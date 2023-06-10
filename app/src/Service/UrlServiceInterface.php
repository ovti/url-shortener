<?php
/**
 * Url Interface.
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
     *
     * @param int  $page  Page number
     * @param User $users User entity
     *
     * @return PaginationInterface Paginated urls
     */
    public function getPaginatedList(int $page, User $users): PaginationInterface;

    /**
     * Get paginated urls for every user.
     *
     * @param int   $page    Page number
     * @param array $filters Filters
     *
     * @return PaginationInterface Paginated urls
     */
    public function getPaginatedListForEveryUser(int $page, array $filters = []): PaginationInterface;

    /**
     * Save url.
     *
     * @param Url $url Url entity
     */
    public function save(Url $url): void;

    /**
     * Delete url.
     *
     * @param Url $url Url entity
     */
    public function delete(Url $url): void;

    /**
     * Generate short url.
     */
    public function generateShortUrl(): string;
}
