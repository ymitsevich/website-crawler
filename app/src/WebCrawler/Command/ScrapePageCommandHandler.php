<?php

namespace App\WebCrawler\Command;

use App\WebCrawler\Exception\PageFetchException;
use App\WebCrawler\Exception\PageSaveException;
use App\WebCrawler\PageFetcher\PageFetcher;
use App\WebCrawler\PageSaver\PageSaver;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class ScrapePageCommandHandler
{
    public function __construct(private PageFetcher $pageFetcher, private PageSaver $pageSaver)
    {
    }

    public function __invoke(ScrapePageCommand $command): void
    {
        $link = $command->getLink();
        $mainLink = $command->getMainLink();

        try {
            $page = $this->pageFetcher->getByLink($link);
        } catch (PageFetchException) {
            //@todo log
            return;
        }

        try {
            $this->pageSaver->save($page, $mainLink);
        } catch (PageSaveException $e) {
            return;
        }
    }
}
