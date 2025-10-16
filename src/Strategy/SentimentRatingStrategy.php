<?php
namespace App\Strategy;

use Sentiment\Analyzer;

class SentimentRatingStrategy implements RatingStrategyInterface
{
    private Analyzer $analyzer;

    public function __construct()
    {
        $this->analyzer = new Analyzer();
    }

    public function calculateRating(string $text): ?int
    {
        try {
            $sentiment = $this->analyzer->getSentiment($text);
            return $this->convertSentimentToRating($sentiment);

        } catch (\Exception $e) {
            return null;
        }
    }

    private function convertSentimentToRating(array $sentiment): int
    {
        $score = $sentiment['score'] ?? 0;
        $normalized = (($score + 4) / 8) * 4 + 1;
        return (int) round(max(1, min(10, $normalized)));
    }
}
