<?php

namespace App\WebCrawler\Service;

use App\WebCrawler\Command\ScrapePageCommand;
use App\WebCrawler\Exception\ValidationException;
use App\WebCrawler\Link\LinkSeeker;
use App\WebCrawler\Link\Validator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class WebsiteCrawlerService
{
    public function __construct(
        private LinkSeeker $linkSeeker,
        private Validator $validator,
        private MessageBusInterface $bus,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @throws ValidationException
     */
    public function process(string $mainLink): void
    {
        if (!$this->validator->isValidLink($mainLink)) {
            $this->logger->error("Invalid main link. [$mainLink]");

            throw new ValidationException();
        }
        $links = $this->linkSeeker->getByLink($mainLink);
        foreach ($links as $link) {
            $this->bus->dispatch(new ScrapePageCommand($link, $mainLink));
        }
    }
}
