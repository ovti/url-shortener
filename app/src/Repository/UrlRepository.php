<?php
/**
 * Url repository.
 */

namespace App\Repository;

use App\Entity\Tag;
use App\Entity\Url;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Url|null find($id, $lockMode = null, $lockVersion = null)
 * @method Url|null findOneBy(array $criteria, array $orderBy = null)
 * @method Url[]    findAll()
 * @method Url[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UrlRepository extends ServiceEntityRepository
{
    public const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * UrlRepository constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Url::class);
    }

    /**
     * Query all records.
     *
     * @param array<string, object> $filters Filters
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(array $filters): QueryBuilder
    {
        $this->checkBlockExpiration();

        $queryBuilder = $this->getOrCreateQueryBuilder()
            ->select(
                'partial url.{id, longUrl, shortUrl, createTime, isBlocked, blockExpiration}',
                'partial tags.{id, name}',
            )
            ->leftJoin('url.tags', 'tags')
            ->orderBy('url.createTime', 'DESC');

        return $this->applyFiltersToList($queryBuilder, $filters);
    }

    /**
     * Check if any url block has expired.
     */
    public function checkBlockExpiration(): void
    {
        $queryBuilder = $this->getOrCreateQueryBuilder()
            ->update(Url::class, 'url')
            ->set('url.isBlocked', 'false')
            ->set('url.blockExpiration', 'null')
            ->where('url.blockExpiration < :now')
            ->setParameter('now', new \DateTime('now'));

        $queryBuilder->getQuery()->execute();
    }

    /**
     * Query by author.
     *
     * @param UserInterface         $user    User entity
     * @param array<string, object> $filters Filters
     *
     * @return QueryBuilder Query builder
     */
    public function queryByAuthor(UserInterface $user, array $filters = []): QueryBuilder
    {
        $queryBuilder = $this->queryAll($filters);

        $queryBuilder->andWhere('url.users = :users')
            ->setParameter('users', $user);

        return $queryBuilder;
    }

    /**
     * Query not blocked urls.
     *
     * @param array<string, object> $filters Filters
     *
     * @return QueryBuilder Query builder
     */
    public function queryNotBlocked(array $filters = []): QueryBuilder
    {
        $queryBuilder = $this->queryAll($filters);

        $queryBuilder->andWhere('url.isBlocked = :isBlocked')
            ->setParameter('isBlocked', false);

        return $queryBuilder;
    }

    /**
     * Save record.
     *
     * @param Url $url Url entity
     */
    public function save(Url $url): void
    {
        $this->_em->persist($url);
        $this->_em->flush();
    }

    /**
     * Delete entity.
     *
     * @param Url $url Url entity
     */
    public function delete(Url $url): void
    {
        $this->_em->remove($url);
        $this->_em->flush();
    }

    /**
     * Apply filters to paginated list.
     *
     * @param QueryBuilder          $queryBuilder Query builder
     * @param array<string, object> $filters      Filters array
     *
     * @return QueryBuilder Query builder
     */
    private function applyFiltersToList(QueryBuilder $queryBuilder, array $filters = []): QueryBuilder
    {
        if (isset($filters['tag']) && $filters['tag'] instanceof Tag) {
            $queryBuilder->andWhere('tags IN (:tag)')
                ->setParameter('tag', $filters['tag']);
        }

        return $queryBuilder;
    }

    /**
     * Get or create new query builder.
     *
     * @param QueryBuilder|null $queryBuilder Query builder
     *
     * @return QueryBuilder Query builder
     */
    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('url');
    }
}
