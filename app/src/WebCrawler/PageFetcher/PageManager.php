<?php

namespace App\WebCrawler\PageFetcher;

interface PageManager
{
    public function getLinksByLink(string $targetLink): ?array;
}
