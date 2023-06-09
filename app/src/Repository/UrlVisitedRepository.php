<?php

namespace App\Repository;

use App\Entity\UrlVisited;
use App\Entity\Url;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UrlVisited>
 *
 * @method UrlVisited|null find($id, $lockMode = null, $lockVersion = null)
 * @method UrlVisited|null findOneBy(array $criteria, array $orderBy = null)
 * @method UrlVisited[]    findAll()
 * @method UrlVisited[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UrlVisitedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UrlVisited::class);
    }

    public function countAllVisitsForUrl(): array
    {
        $queryBuilder = $this->getOrCreateQueryBuilder()
            ->select('count(urlVisited.id) as visits, url.long_url')
            ->leftJoin('urlVisited.url', 'url')
            ->groupBy('urlVisited.url', 'url.long_url')
            ->orderBy('visits', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('urlVisited');
    }

    /**
     * Save url visited.
     *
     * @param \App\Entity\UrlVisited $urlVisited UrlVisited entity
     */
    public function save(UrlVisited $urlVisited): void
    {
        $this->_em->persist($urlVisited);
        $this->_em->flush();
    }
}
