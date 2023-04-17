<?php

namespace App\WebCrawler\Link;

class LinkNormalizer
{
    public function process(mixed $link, string $mainLink): string
    {
        if (!str_starts_with($link, '/')) {
            return $link;
        }

        $link = str_ends_with($mainLink, '/') ? substr($link, 1) : $link;

        return $mainLink . $link;
    }
}
