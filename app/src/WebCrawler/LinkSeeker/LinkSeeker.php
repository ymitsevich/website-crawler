<?php

namespace App\WebCrawler\LinkSeeker;

interface LinkSeeker
{

    public function getByLink(string $mainLink): array;
}
