<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Psr\Log\LoggerInterface;

class RickAndMortyApiService
{
    private const BASE_URL = 'https://rickandmortyapi.com/api';

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger
    ) {
    }

    public function getAllCharacters(): array
    {
        $characters = [];
        $page = 1;
        $hasNextPage = true;

        while ($hasNextPage) {
            try {
                $response = $this->httpClient->request('GET', self::BASE_URL . '/character', [
                    'query' => ['page' => $page]
                ]);

                $data = $response->toArray();

                $characters = array_merge($characters, $data['results']);

                $hasNextPage = !empty($data['info']['next']);
                $page++;
                usleep(200000); // 200ms

            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                break;
            }
        }

        return $characters;
    }

    public function getAllEpisodes(): array
    {
        $episodes = [];
        $page = 1;
        $hasNextPage = true;

        while ($hasNextPage) {
            try {
                $response = $this->httpClient->request('GET', self::BASE_URL . '/episode', [
                    'query' => ['page' => $page]
                ]);

                $data = $response->toArray();

                $episodes = array_merge($episodes, $data['results']);

                $hasNextPage = !empty($data['info']['next']);
                $page++;
                usleep(200000);

            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                break;
            }
        }

        return $episodes;
    }

    /**
     * Парсит номер сезона и эпизода из строки эпизода
     */
    public function parseSeasonAndEpisode(string $episodeCode): array
    {
        if (preg_match('/S(\d+)E(\d+)/', $episodeCode, $matches)) {
            return [(int)$matches[1], (int)$matches[2]];
        }
        return [1, 1];
    }
}
