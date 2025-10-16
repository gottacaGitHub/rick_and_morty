<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateReviewDto
{
    #[Assert\NotBlank(message: 'Автор не может быть пустым')]
    #[Assert\Length(
        min: 2,
        max: 255,
    )]
    public string $author;

    #[Assert\NotBlank(message: 'Отзыв не может быть пустым')]
    #[Assert\Length(
        min: 10,
        max: 5000,
    )]
    public string $text;
}
