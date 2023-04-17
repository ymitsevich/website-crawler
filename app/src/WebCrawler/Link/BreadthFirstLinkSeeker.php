<?php

namespace App\WebCrawler\Link;

use App\WebCrawler\PageFetcher\PageManager;

readonly class BreadthFirstLinkSeeker implements LinkSeeker
{
    public function __construct(
        private int $maxLinks,
        private PageManager $pageManager,
        private Validator $validator,
        private LinkNormalizer $linkNormalizer,
    ) {
    }

    public function getByLink(string $mainLink): array
    {
        $collectedLinks = [$mainLink];

        $i = 0;
        while ($i < count($collectedLinks) && count($collectedLinks) < $this->maxLinks) {
            $currentPage = $collectedLinks[$i];

            $newLinks = $this->pageManager->getLinksByLink($currentPage);
            if (!$newLinks) {
                $i++;
                continue;
            }

            foreach ($newLinks as $newLink) {
                $newLink = $this->linkNormalizer->process($newLink, $mainLink);
                if ($this->validator->isValidLink($newLink, $mainLink) &&
                    !in_array($newLink, $collectedLinks)
                    && count($collectedLinks) < $this->maxLinks) {
                    $collectedLinks[] = $newLink;
                }
            }

            $i++;
        }

        return $collectedLinks;
    }
}
