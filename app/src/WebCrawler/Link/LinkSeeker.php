<?php

namespace App\WebCrawler\Link;

interface LinkSeeker
{

    public function getByLink(string $mainLink): array;
}
