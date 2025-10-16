<?php

namespace App\Service;

use App\Entity\Episode;
use App\Entity\Review;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReviewService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ReviewRepository $reviewRepository,
        private ValidatorInterface $validator,
        private RatingCalculator $ratingCalculator
    ) {
    }

    public function createReview(Episode $episode, string $author, string $text): Review
    {
        $review = new Review();
        $review->setEpisode($episode);
        $review->setAuthor($author);
        $review->setText($text);
        $review->setPublishedAt(new \DateTimeImmutable());
        $errors = $this->validator->validate($review);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
            throw new \InvalidArgumentException(implode(', ', $errorMessages));
        }

        $this->entityManager->persist($review);
        $this->entityManager->flush();

        return $review;
    }

    public function getReviewsForEpisode(Episode $episode, int $page = 1, int $limit = 10): array
    {
        return $this->reviewRepository->findWithPagination($page, $limit, [
            'episode' => $episode
        ]);
    }

    public function getEpisodeReviewStats(Episode $episode): array
    {
        return $this->reviewRepository->getReviewStats($episode);
    }

    public function recalculateMissingRatingsWithStrategy(string $strategy): int
    {
        $reviewsWithoutRating = $this->reviewRepository->findBy(['rating' => null]);
        $updatedCount = 0;

        foreach ($reviewsWithoutRating as $review) {
            $rating = $this->ratingCalculator->calculateRating($review->getText(), $strategy);
            $review->setRating($rating);
            $updatedCount++;
        }
        $this->entityManager->flush();
        return $updatedCount;
    }

    public function recalculateAllRatingsWithStrategy(string $strategy): int
    {
        $allReviews = $this->reviewRepository->findAll();
        $updatedCount = 0;

        foreach ($allReviews as $review) {
            $rating = $this->ratingCalculator->calculateRating($review->getText(), $strategy);
            $review->setRating($rating);
            $updatedCount++;
        }
        $this->entityManager->flush();
        return $updatedCount;
    }


}
