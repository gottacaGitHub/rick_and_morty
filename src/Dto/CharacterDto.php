<?php

namespace App\Dto;

class CharacterDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $status,
        public string $gender,
        public array $episodes = []
    ) {
    }
}
