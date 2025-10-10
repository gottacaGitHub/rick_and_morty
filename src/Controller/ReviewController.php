<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Episode;
use App\Entity\Review;
use App\Service\RatingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
final class ReviewController extends AbstractController
{
    #[Route('/episodes/{id}/reviews', name: 'api_review_create', methods: ['POST'])]
    public function create(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        RatingService $ratingService,
        ValidatorInterface $validator
    ): JsonResponse {
        $episode = $entityManager->getRepository(Episode::class)->find($id);

        if (!$episode) {
            return $this->json(['error' => 'Не найдено'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $review = new Review();
        $review->setAuthor($data['author'] ?? '');
        $review->setText($data['text'] ?? '');
        $review->setRating($ratingService->calculateRating($data['text'] ?? ''));
        $review->addEpisode($episode);

        $errors = $validator->validate($review);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        $entityManager->persist($review);
        $entityManager->flush();

        return $this->json([
            'id' => $review->getId(),
            'author' => $review->getAuthor(),
            'text' => $review->getText(),
            'rating' => $review->getRating(),
            'publicationDate' => $review->getPublicationDate()->format('Y-m-d H:i:s'),
        ], 201);
    }
}
