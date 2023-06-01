<?php

namespace App\Repository;

use App\Entity\UrlVisited;
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

    public function findMostVisitedUrls(): QueryBuilder
    {
        $queryBuilder = $this->getOrCreateQueryBuilder()
            ->select(
                'partial url_visited.{id, url, count}',
                'partial url.{id, long_url, short_url, create_time, is_blocked, block_expiration}',
            )
            ->leftJoin('url_visited.url', 'url')
            ->orderBy('url_visited.count', 'DESC');


        return $queryBuilder;
    }

    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('url');
    }


    public function save(UrlVisited $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UrlVisited $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return UrlVisited[] Returns an array of UrlVisited objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UrlVisited
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
