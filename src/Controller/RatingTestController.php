<?php

namespace App\Controller;

use App\Repository\ReviewRepository;
use App\Service\ReviewService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class RatingTestController extends AbstractController
{
    #[Route('/rating-test', name: 'app_rating_test')]
    public function testRatings(
        ReviewRepository $reviewRepository,
        ReviewService $reviewService
    ): JsonResponse {
        $ratingStats = $reviewRepository->createQueryBuilder('r')
            ->select([
                'COUNT(r.id) as total_reviews',
                'COUNT(r.rating) as reviews_with_rating',
                'AVG(r.rating) as average_rating',
                'MIN(r.rating) as min_rating',
                'MAX(r.rating) as max_rating'
            ])
            ->getQuery()
            ->getSingleResult();


        return $this->json([
            'Статистика' => [
                'Всего отзывов' => (int) $ratingStats['total_reviews'],
                'С рейтингом' => (int) $ratingStats['reviews_with_rating'],
                'Без рейтинга' => (int) $ratingStats['total_reviews'] - (int) $ratingStats['reviews_with_rating'],
                'Средний рейтинг' => $ratingStats['average_rating'] ? round((float) $ratingStats['average_rating'], 2) : null,
                'min' => $ratingStats['min_rating'] ? (int) $ratingStats['min_rating'] : null,
                'max' => $ratingStats['max_rating'] ? (int) $ratingStats['max_rating'] : null,
            ],
        ]);
    }
}
