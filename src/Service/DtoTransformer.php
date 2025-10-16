<?php

namespace App\Service;

use App\Dto\CharacterDto;
use App\Dto\EpisodeDto;
use App\Dto\ReviewDto;
use App\Entity\Character;
use App\Entity\Episode;
use App\Entity\Review;

class DtoTransformer
{
    public function transformCharacter(Character $character): CharacterDto
    {
        $episodes = [];
        foreach ($character->getEpisodes() as $episode) {
            $episodes[] = [
                'id' => $episode->getId(),
                'name' => $episode->getName(),
                'air_date' => $episode->getAirDate()->format('Y-m-d'),
                'season' => $episode->getSeason(),
                'episode' => $episode->getEpisode(),
            ];
        }

        return new CharacterDto(
            id: $character->getId(),
            name: $character->getName(),
            status: $character->getStatus()->value,
            gender: $character->getGender()->value,
            episodes: $episodes
        );
    }

    public function transformEpisode(Episode $episode, ?array $reviewStats = null): EpisodeDto
    {
        $reviews = [];
        foreach ($episode->getReviews() as $review) {
            $reviews[] = [
                'id' => $review->getId(),
                'author' => $review->getAuthor(),
                'text' => $review->getText(),
                'rating' => $review->getRating(),
                'published_at' => $review->getPublishedAt()->format('Y-m-d H:i:s'),
            ];
        }

        $characters = [];
        foreach ($episode->getCharacters() as $character) {
            $characters[] = [
                'id' => $character->getId(),
                'name' => $character->getName(),
                'status' => $character->getStatus()->value,
                'gender' => $character->getGender()->value,
            ];
        }

        return new EpisodeDto(
            id: $episode->getId(),
            name: $episode->getName(),
            airDate: $episode->getAirDate()->format('Y-m-d'),
            season: $episode->getSeason(),
            episode: $episode->getEpisode(),
            reviews: $reviews,
            characters: $characters,
            reviewStats: $reviewStats
        );
    }

    public function transformReview(Review $review): ReviewDto
    {
        return new ReviewDto(
            id: $review->getId(),
            author: $review->getAuthor(),
            text: $review->getText(),
            rating: $review->getRating(),
            publishedAt: $review->getPublishedAt()->format('Y-m-d H:i:s'),
            episodeName: $review->getEpisode()->getName()
        );
    }

    /**
     * @param Character[] $characters
     * @return CharacterDto[]
     */
    public function transformCharacters(array $characters): array
    {
        return array_map([$this, 'transformCharacter'], $characters);
    }

    /**
     * @param Episode[] $episodes
     * @return EpisodeDto[]
     */
    public function transformEpisodes(array $episodes): array
    {
        return array_map([$this, 'transformEpisode'], $episodes);
    }

    /**
     * @param Review[] $reviews
     * @return ReviewDto[]
     */
    public function transformReviews(array $reviews): array
    {
        return array_map([$this, 'transformReview'], $reviews);
    }
}
