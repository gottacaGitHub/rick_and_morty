<?php

namespace App\Service;

use App\Strategy\RatingStrategyInterface;
use Psr\Log\LoggerInterface;

class RatingCalculator
{
    /** @var RatingStrategyInterface[] */
    private array $strategies = [];
    private string $defaultStrategy;

    public function __construct(
        string $defaultStrategy,
    ) {
        $this->defaultStrategy = $defaultStrategy;
    }

    public function addStrategy(string $name, RatingStrategyInterface $strategy): void
    {
        $this->strategies[$name] = $strategy;
    }

    public function calculateRating(string $text, ?string $strategyName = null): ?int
    {
        try {
            $strategyName = $strategyName ?? $this->defaultStrategy;

            if (!isset($this->strategies[$strategyName])) {
                $strategyName = $this->defaultStrategy;
            }

            $strategy = $this->strategies[$strategyName];
            return $strategy->calculateRating($text);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getAvailableStrategies(): array
    {
        return array_keys($this->strategies);
    }

    public function getDefaultStrategy(): string
    {
        return $this->defaultStrategy;
    }
}
