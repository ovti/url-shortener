<?php
/**
 * Task service interface.
 */

namespace App\Service;

use App\Entity\Category;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface TaskServiceInterface.
 */
interface CategoryServiceInterface
{
    /**
     * Save entity.
     *
     * @param Category $category Category entity
     */
    public function save(Category $category): void;
    public function getPaginatedList(int $page): PaginationInterface;


}
