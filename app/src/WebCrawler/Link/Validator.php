<?php

namespace App\WebCrawler\Link;

class Validator
{
    public function isValidLink(string $link, ?string $mainLink = null): bool
    {
        if (!preg_match('/^https?:\/\//', $link)) {
            return false;
        }
        if (!$mainLink) {
            return true;
        }

        $linkParts = parse_url($link);
        $mainDomainParts = parse_url($mainLink);

        // Check if the host names match
        if (!str_ends_with($linkParts['host'], $mainDomainParts['host'])) {
            return false;
        }

        // Check if the link is a subpage of the main domain
        if (!str_starts_with($linkParts['path'] ?? '/', $mainDomainParts['path'] ?? '/')) {
            return false;
        }

        return true;
    }
}