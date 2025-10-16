<?php

namespace App\Command;

use App\Entity\Enum\CharacterGender;
use App\Entity\Enum\CharacterStatus;
use App\Service\RickAndMortyApiService;
use App\Entity\Character;
use App\Entity\Episode;
use App\Entity\Review;
use App\Repository\CharacterRepository;
use App\Repository\EpisodeRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(
    name: 'app:import-rickmorty-data'
)]
class ImportRickAndMortyDataCommand extends Command
{

    private Generator $faker;
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RickAndMortyApiService $apiService,
        private CharacterRepository $characterRepository,
        private EpisodeRepository $episodeRepository,
        private ReviewRepository $reviewRepository,
        private SerializerInterface $serializer,
    ) {
        parent::__construct();
        $this->faker = Factory::create();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'skip-reviews',
                null,
                InputOption::VALUE_NONE,
                'Skip importing reviews from JSON file'
            )
            ->addOption(
                'reviews-limit',
                null,
                InputOption::VALUE_REQUIRED,
                'Limit number of reviews to import',
                50000
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Импорт данных из API');

        $io->section('Персонажи.');
        $charactersImported = $this->importCharacters($io);

        $io->section('Эпизоды');
        $episodesImported = $this->importEpisodes($io);

        $relationsImported = $this->importCharacterEpisodeRelations($io);

        // Импорт отзывов (если не пропущен)
        $reviewsImported = 0;
        if (!$input->getOption('skip-reviews')) {
            $io->section('Импорт отзывов');
            $reviewsLimit = (int) $input->getOption('reviews-limit');
            $reviewsImported = $this->importReviews($io, $reviewsLimit);
        }

        $io->success(sprintf(
            'Успешно! Персонажей: %d, Эпизодов: %d, Связей между эпизодами и персонажами: %d, Отзывов: %d',
            $charactersImported,
            $episodesImported,
            $relationsImported,
            $reviewsImported
        ));

        return Command::SUCCESS;
    }

    private function importCharacters(SymfonyStyle $io): int
    {
        $charactersData = $this->apiService->getAllCharacters();
        $importedCount = 0;
        $updatedCount = 0;

        $io->progressStart(count($charactersData));

        foreach ($charactersData as $characterData) {
            $apiId = $characterData['id'];
            $existingCharacter = $this->characterRepository->findOneBy(['apiId' => $apiId]);

            $status = CharacterStatus::tryFrom($characterData['status']) ?? CharacterStatus::UNKNOWN;
            $gender = CharacterGender::tryFrom($characterData['gender']) ?? CharacterGender::UNKNOWN;

            if ($existingCharacter) {
                $existingCharacter->setName($characterData['name']);
                $existingCharacter->setStatus($status);
                $existingCharacter->setGender($gender);
                $existingCharacter->setUrl($characterData['url']);
                $updatedCount++;
            } else {
                $character = new Character();
                $character->setApiId($apiId);
                $character->setName($characterData['name']);
                $character->setStatus($status);
                $character->setGender($gender);
                $character->setUrl($characterData['url']);
                $this->entityManager->persist($character);
                $importedCount++;
            }

            $io->progressAdvance();
        }

        $this->entityManager->flush();
        $io->progressFinish();

        $io->text(sprintf('Персонажи: %d новых, %d обновленных', $importedCount, $updatedCount));
        return $importedCount + $updatedCount;
    }

    /**
     * @throws \DateMalformedStringException
     */
    private function importEpisodes(SymfonyStyle $io): int
    {
        $episodesData = $this->apiService->getAllEpisodes();
        $importedCount = 0;
        $updatedCount = 0;

        $io->progressStart(count($episodesData));

        foreach ($episodesData as $episodeData) {
            $apiId = $episodeData['id'];
            $existingEpisode = $this->episodeRepository->findOneBy(['apiId' => $apiId]);
            [$season, $episodeNumber] = $this->apiService->parseSeasonAndEpisode($episodeData['episode']);

            if ($existingEpisode) {
                $existingEpisode->setName($episodeData['name']);
                $existingEpisode->setAirDate(new \DateTime($episodeData['air_date']));
                $existingEpisode->setSeason($season);
                $existingEpisode->setEpisode($episodeNumber);
                $existingEpisode->setUrl($episodeData['url']);
                $updatedCount++;
            } else {
                $episode = new Episode();
                $episode->setApiId($apiId);
                $episode->setName($episodeData['name']);
                $episode->setAirDate(new \DateTime($episodeData['air_date']));
                $episode->setSeason($season);
                $episode->setEpisode($episodeNumber);
                $episode->setUrl($episodeData['url']);
                $this->entityManager->persist($episode);
                $importedCount++;
            }

            $io->progressAdvance();
        }

        $this->entityManager->flush();
        $io->progressFinish();

        $io->text(sprintf('Эпизоды: %d новых, %d обновленных', $importedCount, $updatedCount));
        return $importedCount + $updatedCount;
    }

    private function importReviews(SymfonyStyle $io, int $limit = 50000): int
    {
        $reviewsJsonPath = __DIR__ . '/../../rick_and_morty_reviews_50000_clean.json';

        if (!file_exists($reviewsJsonPath)) {
            $io->warning('Нет файла с отзывами');
            return 0;
        }

        $reviewsJson = file_get_contents($reviewsJsonPath);
        $reviewsData = $this->serializer->decode($reviewsJson, 'json');

        // Ограничиваем количество отзывов если нужно
        if ($limit > 0 && count($reviewsData) > $limit) {
            $reviewsData = array_slice($reviewsData, 0, $limit);
        }

        // Получаем все эпизоды из базы
        $episodes = $this->episodeRepository->findAll();

        if (empty($episodes)) {
            $io->warning('Эпизоды не загружены.');
            return 0;
        }

        $importedCount = 0;
        $batchSize = 100;
        $totalReviews = count($reviewsData);

        $io->progressStart($totalReviews);

        foreach ($reviewsData as $index => $reviewText) {

            $review = new Review();

            $randomEpisode = $episodes[array_rand($episodes)];
            $review->setEpisode($randomEpisode);
            $review->setAuthor($this->generateFakerAuthor());
            $review->setText($reviewText);
            $review->setPublishedAt($this->generateRandomDate());

            $this->entityManager->persist($review);
            $importedCount++;

            if (($importedCount % $batchSize) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                $episodes = $this->episodeRepository->findAll();
            }

            $io->progressAdvance();
        }

        $this->entityManager->flush();
        $io->progressFinish();

        $io->text(sprintf('Отзывов: %d добавлено', $importedCount));

        return $importedCount;
    }

    private function generateFakerAuthor(): string
    {
        return $this->faker->name();
    }

    /**
     * Генерирует случайную дату за последние 2 года
     */
    private function generateRandomDate(): \DateTimeImmutable
    {
        $start = new \DateTimeImmutable('-2 years');
        $end = new \DateTimeImmutable();

        $randomTimestamp = mt_rand($start->getTimestamp(), $end->getTimestamp());

        return (new \DateTimeImmutable())->setTimestamp($randomTimestamp);
    }


    private function importCharacterEpisodeRelations(SymfonyStyle $io): int
    {
        $io->section('Импорт связей между эпизодами и персонажами');

        $episodesData = $this->apiService->getAllEpisodes();
        $relationCount = 0;

        $io->progressStart(count($episodesData));

        foreach ($episodesData as $episodeData) {
            $apiId = $episodeData['id'];
            $episode = $this->episodeRepository->findOneBy(['apiId' => $apiId]);

            if (!$episode) {
                $io->progressAdvance();
                continue;
            }

            foreach ($episode->getCharacters() as $character) {
                $episode->removeCharacter($character);
            }

            foreach ($episodeData['characters'] as $characterUrl) {
                if (preg_match('/character\/(\d+)$/', $characterUrl, $matches)) {
                    $characterApiId = (int)$matches[1];
                    $character = $this->characterRepository->findOneBy(['apiId' => $characterApiId]);

                    if ($character) {
                        $episode->addCharacter($character);
                        $relationCount++;
                    }
                }
            }

            $io->progressAdvance();
        }

        $this->entityManager->flush();
        $io->progressFinish();

        $io->text(sprintf('Успешно %d создано', $relationCount));
        return $relationCount;
    }
}
