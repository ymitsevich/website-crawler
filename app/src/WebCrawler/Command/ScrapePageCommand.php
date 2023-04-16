<?php

namespace App\WebCrawler\Command;

readonly class ScrapePageCommand
{
    public function __construct(private string $link, private string $mainLink)
    {
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function getMainLink(): string
    {
        return $this->mainLink;
    }
}
