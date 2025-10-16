<?php

namespace App\Dto;

class ReviewDto
{
    public function __construct(
        public int $id,
        public string $author,
        public string $text,
        public ?int $rating,
        public string $publishedAt,
        public ?string $episodeName = null
    ) {
    }
}
