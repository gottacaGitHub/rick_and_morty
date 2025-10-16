<?php

namespace App\EntityListener;

use App\Entity\Review;
use App\Service\RatingCalculator;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Review::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Review::class)]
class ReviewListener
{
    public function __construct(
        private RatingCalculator $ratingCalculator
    ) {
    }

    public function prePersist(Review $review): void
    {
        $this->calculateRating($review);
    }

    public function preUpdate(Review $review): void
    {
        $this->calculateRating($review);
    }

    private function calculateRating(Review $review): void
    {
        if ($review->getText() && $review->getRating() === null) {
            $rating = $this->ratingCalculator->calculateRating($review->getText());
            $review->setRating($rating);
        }
    }
}
