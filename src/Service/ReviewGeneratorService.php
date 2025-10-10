<?php

namespace App\Service;

use App\Entity\Episode;
use App\Entity\Review;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;

class ReviewGeneratorService
{
    private EntityManagerInterface $entityManager;
    private RatingService $ratingService;

    public function __construct(EntityManagerInterface $entityManager, RatingService $ratingService)
    {
        $this->entityManager = $entityManager;
        $this->ratingService = $ratingService;
    }

    public function generateReviewsFromJson(string $jsonFilePath): void
    {
        if (!file_exists($jsonFilePath)) {
            throw new \RuntimeException("JSON file not found: $jsonFilePath");
        }

        $reviewsData = json_decode(file_get_contents($jsonFilePath), true);
        $episodes = $this->entityManager->getRepository(Episode::class)->findAll();
        $faker = Factory::create();

        foreach ($episodes as $episode) {
            $reviewsCount = rand(50, 500);

            for ($i = 0; $i < $reviewsCount; $i++) {
                $reviewData = $reviewsData[array_rand($reviewsData)];

                $review = new Review();
                $review->setAuthor($faker->name);
                $review->setText($reviewData['text']);
                $review->setRating($this->ratingService->calculateRating($reviewData['text']));
                $review->setEpisode($episode);

                $this->entityManager->persist($review);
            }
        }

        $this->entityManager->flush();
    }
}
