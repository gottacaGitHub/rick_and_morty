<?php

namespace App\Dto;

class EpisodeDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $airDate,
        public int $season,
        public int $episode,
        public array $reviews = [],
        public array $characters = [],
        public ?array $reviewStats = null
    ) {
    }
}
