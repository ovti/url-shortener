<?php

namespace App\Repository;

use App\Entity\GuestUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use DateTimeImmutable;
use App\Entity\Url;

/**
 * @extends ServiceEntityRepository<GuestUser>
 *
 * @method GuestUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method GuestUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method GuestUser[]    findAll()
 * @method GuestUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GuestUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GuestUser::class);
    }

    /**
     * Save guest user.
     *
     * @param \App\Entity\GuestUser $guestUser GuestUser entity
     */
    public function save(GuestUser $guestUser): void
    {
        $this->_em->persist($guestUser);
        $this->_em->flush();
    }

    //count how many times email from guest_user table appears in last 24 hours in url table, guest_user_id is foreign key in url table
    public function countUrlsCreatedInLast24Hours(string $email): int
    {
        $queryBuilder = $this->getOrCreateQueryBuilder();

        $queryBuilder->select('count(url.id)')
            ->from(Url::class, 'url')
            ->leftJoin('url.guest_user', 'guest_user')
            ->where('guest_user.email = :email')
            ->andWhere('url.create_time >= :date')
            ->setParameter('email', $email)
            ->setParameter('date', new DateTimeImmutable('-24 hours'));

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('urlVisited');
    }
}
