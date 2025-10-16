<?php

namespace App\Controller\Api;

use App\Dto\CreateReviewDto;
use App\Entity\Episode;
use App\Service\DtoTransformer;
use App\Service\ReviewService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/episodes/{id}/reviews')]
class ReviewController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private DtoTransformer $dtoTransformer
    ) {
    }

    #[Route('', name: 'api_reviews_create', methods: ['POST'])]
    public function create(
        Episode $episode,
        Request $request,
        ReviewService $reviewService
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->json([
                    'error' => 'Проверьте данные'
                ], 400);
            }

            $createReviewDto = new CreateReviewDto();
            $createReviewDto->author = $data['author'] ?? '';
            $createReviewDto->text = $data['text'] ?? '';

            // Валидация DTO
            $errors = $this->validator->validate($createReviewDto);

            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
                }

                return $this->json([
                    'error' => 'Проверьте данные',
                    'messages' => $errorMessages
                ], 400);
            }

            $review = $reviewService->createReview(
                $episode,
                $createReviewDto->author,
                $createReviewDto->text
            );

            $reviewDto = $this->dtoTransformer->transformReview($review);

            return $this->json([
                'data' => $reviewDto,
                'message' => 'Отзыв добавлен'
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Сервер недоступен: ' . $e->getMessage()
            ], 500);
        }
    }
}
