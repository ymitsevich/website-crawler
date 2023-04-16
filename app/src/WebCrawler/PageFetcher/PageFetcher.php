<?php

namespace App\WebCrawler\PageFetcher;

use App\WebCrawler\Dto\Page;
use App\WebCrawler\Exception\PageFetchException;

interface PageFetcher
{
    /**
     * @throws PageFetchException
     */
    public function getByLink(string $targetLink): Page;
}
