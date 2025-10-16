<?php

namespace App\Repository;

use App\Entity\Episode;
use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function save(Review $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Review $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findWithPagination(int $page = 1, int $limit = 10, array $criteria = []): array
    {
        $offset = ($page - 1) * $limit;

        $qb = $this->createQueryBuilder('r');

        foreach ($criteria as $field => $value) {
            $qb->andWhere("r.{$field} = :{$field}")
                ->setParameter($field, $value);
        }

        return $qb->orderBy('r.publishedAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getReviewStats(Episode $episode): array
    {
        $result = $this->createQueryBuilder('r')
            ->select([
                'COUNT(r.id) as total_reviews',
                'AVG(r.rating) as average_rating',
                'MIN(r.publishedAt) as first_review_date',
                'MAX(r.publishedAt) as last_review_date'
            ])
            ->where('r.episode = :episode')
            ->setParameter('episode', $episode)
            ->getQuery()
            ->getSingleResult();

        return [
            'total_reviews' => (int) $result['total_reviews'],
            'average_rating' => $result['average_rating'] ? round((float) $result['average_rating'], 2) : null,
            'first_review_date' => $result['first_review_date'],
            'last_review_date' => $result['last_review_date'],
        ];
    }
}
