<?php

namespace App\Repository;

use App\Entity\Url;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class UrlRepository extends ServiceEntityRepository
{
    public const PAGINATOR_ITEMS_PER_PAGE = 10;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Url::class);
    }

    public function queryAll(): QueryBuilder
    {
//        return $this->getOrCreateQueryBuilder()
//            ->orderBy('url.id', 'ASC');
        return $this->getOrCreateQueryBuilder()
            ->select(
                'partial url.{id, long_url, short_url, create_time, is_blocked, block_expiration}',
                'partial tag.{id, name}'
            )
            ->leftJoin('url.tags', 'tag')
            ->orderBy('url.id', 'ASC');
    }

    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('url');
    }

    public function save(Url $url): void
    {
        $this->_em->persist($url);
        $this->_em->flush();
    }
}
