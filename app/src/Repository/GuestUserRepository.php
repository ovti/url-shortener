<?php
/**
 * GuestUser repository.
 */

namespace App\Repository;

use App\Entity\GuestUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class GuestUserRepository.
 *
 * @method GuestUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method GuestUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method GuestUser[]    findAll()
 * @method GuestUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GuestUserRepository extends ServiceEntityRepository
{
    /**
     * GuestUserRepository constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GuestUser::class);
    }

    /**
     * Save guest user.
     *
     * @param GuestUser $guestUser GuestUser entity
     */
    public function save(GuestUser $guestUser): void
    {
        $this->_em->persist($guestUser);
        $this->_em->flush();
    }

    /**
     * Count urls created in last 24 hours for given email.
     *
     * @param string $email Email
     *
     * @return int Result
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countEmailsUsedInLast24Hours(string $email): int
    {
        $queryBuilder = $this->getOrCreateQueryBuilder()
            ->select('count(guestUser.id)')
            ->leftJoin('App\Entity\Url', 'url', 'WITH', 'url.guestUser = guestUser')
            ->where('guestUser.email = :email')
            ->andWhere('url.createTime > :time')
            ->setParameter('email', $email)
            ->setParameter('time', new \DateTimeImmutable('-24 hours'));

        return $queryBuilder->getQuery()->getSingleScalarResult();
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
        return $queryBuilder ?? $this->createQueryBuilder('guestUser');
    }
}
