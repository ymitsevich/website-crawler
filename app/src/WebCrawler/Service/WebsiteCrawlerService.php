<?php

namespace App\WebCrawler\Service;

use App\WebCrawler\Command\ScrapePageCommand;
use App\WebCrawler\LinkSeeker\LinkSeeker;
use App\WebCrawler\Validator\Validator;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class WebsiteCrawlerService
{
    public function __construct(
        private LinkSeeker $linkSeeker,
        private Validator $validator,
        private MessageBusInterface $bus,
    ) {
    }

    public function process(string $mainLink): void
    {
        if (!$this->validator->isValidLink($mainLink)) {
            //@todo log, exception

            return;
        }
        $links = $this->linkSeeker->getByLink($mainLink);
        foreach ($links as $link) {
            $this->bus->dispatch(new ScrapePageCommand($link, $mainLink));
        }
    }
}
