<?php

namespace App\Command;

use AllowDynamicProperties;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Service\ReviewGeneratorService;

#[AllowDynamicProperties]
#[AsCommand(
    name: 'app:generate-reviews',
    description: 'Add a short description for your command',
)]
class GenerateReviewsCommand extends Command
{
    public function __construct(ReviewGeneratorService $reviewGenerator)
    {
        parent::__construct();
        $this->reviewGenerator = $reviewGenerator;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('file');

        try {
            $io->title('Отзывы из json');
            $this->reviewGenerator->generateReviewsFromJson($filePath);
            $io->success('Успешно!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Ощибка' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
