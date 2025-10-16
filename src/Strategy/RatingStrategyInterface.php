<?php

namespace App\Strategy;

interface RatingStrategyInterface
{
    public function calculateRating(string $text): ?int;
}
