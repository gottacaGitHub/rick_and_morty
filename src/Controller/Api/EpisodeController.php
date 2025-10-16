<?php

namespace App\Controller\Api;

use App\Entity\Episode;
use App\Repository\EpisodeRepository;
use App\Service\DtoTransformer;
use App\Service\ReviewService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/episodes')]
class EpisodeController extends AbstractController
{
    public function __construct(
        private DtoTransformer $dtoTransformer
    ) {
    }

    #[Route('', name: 'api_episodes_list', methods: ['GET'])]
    public function list(
        Request $request,
        EpisodeRepository $episodeRepository
    ): JsonResponse {
        $offset = $request->query->getInt('offset', 0);
        $limit = $request->query->getInt('limit', 10);
        $season = $request->query->get('season');
        $search = $request->query->get('search');

        $characterId = $request->query->getInt('character_id', 0) ?: null;
        $airDateFrom = $request->query->get('air_date_from') ?
            \DateTime::createFromFormat('Y-m-d', $request->query->get('air_date_from')) : null;
        $airDateTo = $request->query->get('air_date_to') ?
            \DateTime::createFromFormat('Y-m-d', $request->query->get('air_date_to')) : null;

        $sortBy = $request->query->get('sort_by');
        $sortOrder = $request->query->get('sort_order', 'ASC');

        $limit = min($limit, 100);

        $episodes = $episodeRepository->findWithPaginationAndFilters(
            $offset,
            $limit,
            $season,
            $search,
            $characterId,
            $airDateFrom,
            $airDateTo,
            $sortBy,
            $sortOrder
        );

        $total = $episodeRepository->countWithFilters(
            $season,
            $search,
            $characterId,
            $airDateFrom,
            $airDateTo
        );

        $episodeDtos = $this->dtoTransformer->transformEpisodes($episodes);

        return $this->json([
            'data' => $episodeDtos,
            'pagination' => [
                'offset' => $offset,
                'limit' => $limit,
                'total' => $total,
            ],
            'filters' => [
                'Сезон' => $season,
                'Поиск по' => $search,
                'Персонаж id' => $characterId,
                'air_date_from' => $airDateFrom?->format('Y-m-d'),
                'air_date_to' => $airDateTo?->format('Y-m-d'),
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ]
        ]);
    }

    #[Route('/{id}', name: 'api_episodes_show', methods: ['GET'])]
    public function show(
        Episode $episode,
        ReviewService $reviewService
    ): JsonResponse {
        $reviewStats = $reviewService->getEpisodeReviewStats($episode);
        $episodeDto = $this->dtoTransformer->transformEpisode($episode, $reviewStats);

        return $this->json([
            'data' => $episodeDto
        ]);
    }

    #[Route('/{id}/reviews', name: 'api_episodes_reviews', methods: ['GET'])]
    public function reviews(
        Episode $episode,
        Request $request,
        ReviewService $reviewService
    ): JsonResponse {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $reviews = $reviewService->getReviewsForEpisode($episode, $page, $limit);
        $reviewStats = $reviewService->getEpisodeReviewStats($episode);

        $reviewDtos = $this->dtoTransformer->transformReviews($reviews);

        return $this->json([
            'data' => $reviewDtos,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $reviewStats['total_reviews'],
            ],
            'review_stats' => $reviewStats,
        ]);
    }
}
