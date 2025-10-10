<?php

namespace App\Service;

use App\Entity\Character;
use App\Entity\Episode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RickAndMortyApiService
{
    private HttpClientInterface $httpClient;
    private EntityManagerInterface $entityManager;

    public function __construct(HttpClientInterface $httpClient, EntityManagerInterface $entityManager)
    {
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
    }

    public function importCharacters(): void
    {
        $page = 1;
        do {
            $response = $this->httpClient->request('GET', 'https://rickandmortyapi.com/api/character', [
                'query' => ['page' => $page]
            ]);

            $data = $response->toArray();

            foreach ($data['results'] as $characterData) {
                $character = $this->entityManager->getRepository(Character::class)
                    ->findOneBy(['url' => $characterData['url']]);

                if (!$character) {
                    $character = new Character();
                    $character->setName($characterData['name']);
                    $character->setGender($characterData['gender']);
                    $character->setStatus($characterData['status']);
                    $character->setUrl($characterData['url']);

                    $this->entityManager->persist($character);
                }
            }

            $this->entityManager->flush();
            $page++;

        } while ($data['info']['next'] !== null);
    }

    public function importEpisodes(): void
    {
        $page = 1;
        do {
            $response = $this->httpClient->request('GET', 'https://rickandmortyapi.com/api/episode', [
                'query' => ['page' => $page]
            ]);

            $data = $response->toArray();

            foreach ($data['results'] as $episodeData) {
                $episode = $this->entityManager->getRepository(Episode::class)
                    ->findOneBy(['url' => $episodeData['url']]);

                if (!$episode) {
                    $episode = new Episode();
                    $episode->setName($episodeData['name']);
                    $episode->setAirDate(new \DateTime($episodeData['air_date']));

                    // Парсим сезон и номер эпизода
                    preg_match('/S(\d+)E(\d+)/', $episodeData['episode'], $matches);
                    $episode->setSeason('Season ' . $matches[1]);
                    $episode->setEpisodeNumber('Episode ' . $matches[2]);
                    $episode->setUrl($episodeData['url']);

                    $this->entityManager->persist($episode);

                    // Добавляем персонажей к эпизоду
                    $this->addCharactersToEpisode($episode, $episodeData['characters']);
                }
            }

            $this->entityManager->flush();
            $page++;

        } while ($data['info']['next'] !== null);
    }

    private function addCharactersToEpisode(Episode $episode, array $characterUrls): void
    {
        foreach ($characterUrls as $characterUrl) {
            $character = $this->entityManager->getRepository(Character::class)
                ->findOneBy(['url' => $characterUrl]);

            if ($character && !$episode->getCharacters()->contains($character)) {
                $episode->addCharacter($character);
            }
        }
    }
}
