<?php

namespace App\WebCrawler\CliCommand;

use App\WebCrawler\Service\WebsiteCrawlerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'scrape:website:run',
    description: 'Add a short description for your command',
)]
class ScrapeWebsiteCommand extends Command
{
    public function __construct(private readonly WebsiteCrawlerService $service, string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument(
            'targetLink',
            InputArgument::OPTIONAL,
            'Target link to start crawling',
            'https://spiegel.de'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $targetLink = $input->getArgument('targetLink');

        if ($targetLink) {
            $io->note(sprintf('You passed an argument: %s', $targetLink));
        }

        $this->service->process($targetLink);

        $io->success('The jobs have been put into the queue.');

        return Command::SUCCESS;
    }
}
