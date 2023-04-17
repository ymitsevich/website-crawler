<?php

namespace App\WebCrawler\PageFetcher;

use DOMDocument;
use Psr\Log\LoggerInterface;
use Throwable;

readonly class HtmlPageManager implements PageManager
{
    public function __construct(
        private PageFetcher $pageFetcher,
        private LoggerInterface $logger
    ) {
    }

    public function getLinksByLink(string $targetLink): ?array
    {
        try {
            $page = $this->pageFetcher->getByLink($targetLink);

            // @todo use more complex dom loader to get dynamic pages with js
            $dom = new DOMDocument();
            @$dom->loadHTML($page->getContent());
            $domLinks = $dom->getElementsByTagName('a');
            $newLinks = [];
            foreach ($domLinks as $domLink) {
                $newLinks[] = $domLink->getAttribute('href');
            }

            return $newLinks;
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());

            return null;
        }
    }
}
