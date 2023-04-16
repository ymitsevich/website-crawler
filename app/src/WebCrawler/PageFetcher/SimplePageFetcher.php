<?php

namespace App\WebCrawler\PageFetcher;

use App\WebCrawler\Dto\Page;
use App\WebCrawler\Exception\PageFetchException;
use App\WebCrawler\Factory\PageFactory;
use Throwable;

readonly class SimplePageFetcher implements PageFetcher
{
    public function __construct(private PageFactory $factory)
    {
    }

    /**
     * @inheritDoc
     */
    public function getByLink(string $targetLink): Page
    {
        try {
            $content = file_get_contents($targetLink);

            return $this->factory->createByContent($content);
        } catch (Throwable $e) {
            throw new PageFetchException(previous: $e);
        }
    }
}
