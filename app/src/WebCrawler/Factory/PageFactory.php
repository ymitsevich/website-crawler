<?php

namespace App\WebCrawler\Factory;

use App\Common\RandomDataGenerator;
use App\WebCrawler\Dto\Page;
use DOMDocument;

readonly class PageFactory
{
    public function __construct(private RandomDataGenerator $randomDataGenerator)
    {
    }

    public function createByContent(string $content): Page
    {
        $domDocument = new DOMDocument();
        @$domDocument->loadHTML($content);
        $domTitles = $domDocument->getElementsByTagName('title');
        $title = trim($domTitles[0] ? $domTitles[0]->textContent : '');
        if ($title) {
            $nameParts[] = $title;
        }
        $domH1s = $domDocument->getElementsByTagName('h1');
        $h1 = trim($domH1s[0] ? $domH1s[0]->textContent : '');
        if ($h1) {
            $nameParts[] = $h1;
        }
        $uuid = $this->randomDataGenerator->createUniqId();
        $nameParts[] = $uuid;
        $name = implode('_', $nameParts);

        return (new Page($name))->setContent(trim($content));
    }
}
