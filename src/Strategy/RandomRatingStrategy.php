<?php

namespace App\Strategy;

class RandomRatingStrategy implements RatingStrategyInterface
{
    public function calculateRating(string $text): ?int
    {
        return random_int(1, 5);
    }
}
