<?php

namespace App\WebCrawler\PageSaver;

use App\WebCrawler\Dto\Page;
use App\WebCrawler\Exception\PageSaveException;

interface PageSaver
{
    /**
     * @throws PageSaveException
     */
    public function save(Page $page, string $additionalDir): void;
}
