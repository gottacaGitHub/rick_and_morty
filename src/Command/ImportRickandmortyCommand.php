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
use App\Service\RickAndMortyApiService;

#[AllowDynamicProperties]
#[AsCommand(
    name: 'app:import-rickandmorty',
    description: 'Add a short description for your command',
)]
class ImportRickandmortyCommand extends Command
{
    public function __construct(RickAndMortyApiService $apiService)
    {
        parent::__construct();
        $this->apiService = $apiService;
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

        $io->title('Импорт данных из Rick and Morty API');

        $io->section('Персонажи.');
        $this->apiService->importCharacters();
        $io->success('Успешно!');

        $io->section('Эпизоды');
        $this->apiService->importEpisodes();
        $io->success('Успешно!');

        return Command::SUCCESS;
    }
}
