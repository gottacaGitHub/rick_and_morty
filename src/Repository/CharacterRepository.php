<?php

namespace App\Repository;

use App\Entity\Character;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Character>
 */
class CharacterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Character::class);
    }

    public function save(Character $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Character $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findWithPaginationAndFilters(
        int $offset = 0,
        int $limit = 10,
        ?string $status = null,
        ?string $gender = null,
        ?string $search = null
    ): array {
        $qb = $this->createQueryBuilder('c');

        if ($status) {
            $qb->andWhere('c.status = :status')
                ->setParameter('status', $status);
        }

        if ($gender) {
            $qb->andWhere('c.gender = :gender')
                ->setParameter('gender', $gender);
        }

        if ($search) {
            $qb->andWhere('c.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return $qb->orderBy('c.name', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countWithFilters(
        ?string $status = null,
        ?string $gender = null,
        ?string $search = null
    ): int {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)');

        if ($status) {
            $qb->andWhere('c.status = :status')
                ->setParameter('status', $status);
        }

        if ($gender) {
            $qb->andWhere('c.gender = :gender')
                ->setParameter('gender', $gender);
        }

        if ($search) {
            $qb->andWhere('c.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
