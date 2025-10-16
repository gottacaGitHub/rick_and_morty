<?php

namespace App\Controller;

use App\Repository\EpisodeRepository;
use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ImportReviewTestController extends AbstractController
{
    #[Route('/import-review-test', name: 'app_import_review_test')]
    public function testImportReviews(
        EpisodeRepository $episodeRepository,
        ReviewRepository $reviewRepository
    ): JsonResponse {
        $totalReviews = $reviewRepository->count([]);
        $episodesWithReviews = $episodeRepository->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->innerJoin('e.reviews', 'r')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->json([
            'Статистика' => [
                'Число отзывов' => $totalReviews,
                'Эпизодов с отзывами' => (int) $episodesWithReviews,
                'Всего эпизодов' => $episodeRepository->count([]),
            ]
        ]);
    }
}
