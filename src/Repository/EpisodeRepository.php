<?php

namespace App\Repository;

use App\Entity\Episode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Episode>
 */
class EpisodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Episode::class);
    }

    public function save(Episode $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Episode $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findWithPaginationAndFilters(
        int $offset = 0,
        int $limit = 10,
        ?string $season = null,
        ?string $search = null,
        ?int $characterId = null,
        ?\DateTimeInterface $airDateFrom = null,
        ?\DateTimeInterface $airDateTo = null,
        ?string $sortBy = null,
        ?string $sortOrder = 'ASC'
    ): array {
        $qb = $this->createQueryBuilder('e');

        if ($season) {
            $qb->andWhere('e.season = :season')
                ->setParameter('season', $season);
        }

        if ($search) {
            $qb->andWhere('e.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($characterId) {
            $qb->innerJoin('e.characters', 'c')
                ->andWhere('c.id = :characterId')
                ->setParameter('characterId', $characterId);
        }

        if ($airDateFrom) {
            $qb->andWhere('e.airDate >= :airDateFrom')
                ->setParameter('airDateFrom', $airDateFrom);
        }

        if ($airDateTo) {
            $qb->andWhere('e.airDate <= :airDateTo')
                ->setParameter('airDateTo', $airDateTo);
        }

        if ($sortBy === 'air_date') {
            $qb->orderBy('e.airDate', $sortOrder);
        } elseif ($sortBy === 'average_rating') {
            $qb->leftJoin('e.reviews', 'r')
                ->addSelect('AVG(r.rating) as HIDDEN avg_rating')
                ->groupBy('e.id')
                ->orderBy('avg_rating', $sortOrder === 'DESC' ? 'DESC' : 'ASC');
        } else {
            $qb->orderBy('e.season', 'ASC')
                ->addOrderBy('e.episode', 'ASC');
        }

        return $qb->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countWithFilters(
        ?string $season = null,
        ?string $search = null,
        ?int $characterId = null,
        ?\DateTimeInterface $airDateFrom = null,
        ?\DateTimeInterface $airDateTo = null
    ): int {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)');

        if ($season) {
            $qb->andWhere('e.season = :season')
                ->setParameter('season', $season);
        }

        if ($search) {
            $qb->andWhere('e.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($characterId) {
            $qb->innerJoin('e.characters', 'c')
                ->andWhere('c.id = :characterId')
                ->setParameter('characterId', $characterId);
        }

        if ($airDateFrom) {
            $qb->andWhere('e.airDate >= :airDateFrom')
                ->setParameter('airDateFrom', $airDateFrom);
        }

        if ($airDateTo) {
            $qb->andWhere('e.airDate <= :airDateTo')
                ->setParameter('airDateTo', $airDateTo);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
