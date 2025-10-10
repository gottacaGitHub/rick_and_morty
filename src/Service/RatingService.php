<?php

namespace App\Service;

use Sentiment\Analyzer;

class RatingService
{
    private string $ratingMethod;
    private ?Analyzer $sentimentAnalyzer;

    public function __construct(string $ratingMethod)
    {
        $this->ratingMethod = $ratingMethod;
        $this->sentimentAnalyzer = $ratingMethod === 'sentiment' ? new Analyzer() : null;
    }

    public function calculateRating(string $text): float
    {
        return match ($this->ratingMethod) {
            'sentiment' => $this->calculateSentimentRating($text),
            'random' => $this->calculateRandomRating(),
            default => throw new \InvalidArgumentException('Unknown rating method: ' . $this->ratingMethod),
        };
    }

    private function calculateSentimentRating(string $text): float
    {
        $scores = $this->sentimentAnalyzer->score_valence($text);
        $compound = $scores['compound'] ?? 0;

        // Преобразуем из диапазона [-1, 1] в [1, 5]
        return round((($compound + 1) / 2) * 4 + 1, 1);
    }

    private function calculateRandomRating(): float
    {
        return round(rand(10, 50) / 10, 1); // От 1.0 до 5.0 с шагом 0.1
    }
}
