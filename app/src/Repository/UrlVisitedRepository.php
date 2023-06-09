<?php
/**
 * UrlVisited repository.
 */

namespace App\Repository;

use App\Entity\UrlVisited;
use App\Entity\Url;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class UrlVisitedRepository.
 *
 * @method UrlVisited|null find($id, $lockMode = null, $lockVersion = null)
 * @method UrlVisited|null findOneBy(array $criteria, array $orderBy = null)
 * @method UrlVisited[]    findAll()
 * @method UrlVisited[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @codeCoverageIgnore
 */
class UrlVisitedRepository extends ServiceEntityRepository
{
    /**
     * UrlVisitedRepository constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UrlVisited::class);
    }

    /**
     * Count all visits for url.
     *
     * @return array Result
     */
    public function countAllVisitsForUrl(): array
    {
        $queryBuilder = $this->getOrCreateQueryBuilder()
            ->select('count(urlVisited.id) as visits, url.longUrl')
            ->leftJoin('urlVisited.url', 'url')
            ->groupBy('urlVisited.url', 'url.longUrl')
            ->orderBy('visits', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Save record.
     *
     * @param UrlVisited $urlVisited UrlVisited entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(UrlVisited $urlVisited): void
    {
        $this->_em->persist($urlVisited);
        $this->_em->flush();
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
        return $queryBuilder ?? $this->createQueryBuilder('urlVisited');
    }
}
