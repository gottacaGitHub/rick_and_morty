<?php

namespace App\Command;

use App\Service\RatingCalculator;
use App\Service\ReviewService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:calculate-ratings'
)]
class CalculateRatingsCommand extends Command
{
    public function __construct(
        private ReviewService $reviewService,
        private RatingCalculator $ratingCalculator,
        private LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $availableStrategies = $this->ratingCalculator->getAvailableStrategies();
        $defaultStrategy = $this->ratingCalculator->getDefaultStrategy();

        $this
            ->addOption(
                'all',
                'a',
                InputOption::VALUE_NONE,
                ''
            )
            ->addOption(
                'strategy',
                's',
                InputOption::VALUE_REQUIRED,
                sprintf(
                    'Доступно: %s, default: %s)',
                    implode(', ', $availableStrategies),
                    $defaultStrategy
                ),
                $defaultStrategy
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Вычисление рейтинга отзывов');

        $recalculateAll = $input->getOption('all');
        $strategy = $input->getOption('strategy');

        $io->note(sprintf(
            "Метод для вычисления: %s (по умолчанию из .env: %s)",
            $strategy,
            $this->ratingCalculator->getDefaultStrategy()
        ));

        if ($recalculateAll) {
            $io->section('Пересчет всего рейтинга');
            $updatedCount = $this->reviewService->recalculateAllRatingsWithStrategy($strategy);
            $io->success("Кол-во пересчитанных отзывов {$updatedCount} использующих метод: {$strategy}");
        } else {
            $io->section('Пересчет рейтинга отзывов, без рейтинга');
            $updatedCount = $this->reviewService->recalculateMissingRatingsWithStrategy($strategy);
            $io->success("Кол-во {$updatedCount} использующих метод: {$strategy}");
        }

        $this->logger->info("Кол-во {$updatedCount} использующих метод: {$strategy}");

        return Command::SUCCESS;
    }
}
