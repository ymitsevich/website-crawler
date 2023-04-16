<?php

namespace App\WebCrawler\PageSaver;

use App\WebCrawler\Dto\Page;
use App\WebCrawler\Exception\PageSaveException;
use Throwable;

readonly class FilesystemPageSaver implements PageSaver
{
    public const DEFAULT_EXTENSION = 'html';

    public function __construct(private string $targetDir)
    {
    }

    /**
     * @inheritDoc
     */
    public function save(Page $page, string $additionalDir): void
    {
        $additionalDir = $this->sanitizeFileName($additionalDir);
        if (!file_exists($this->targetDir . $additionalDir)) {
            mkdir($this->targetDir . $additionalDir);
        }
        $fileName = $this->sanitizeFileName($page->getName());
        $fileFullPath = $this->targetDir . $additionalDir . DIRECTORY_SEPARATOR .
            "$fileName." . self::DEFAULT_EXTENSION;

        try {
            file_put_contents($fileFullPath, $page->getContent());
        } catch (Throwable $e) {
            throw new PageSaveException(previous: $e);
        }
    }

    private function sanitizeFileName(string $dirName): string
    {
        $dirName = preg_replace('/[^a-zA-Z0-9_]/', '-', $dirName);

        $dirName = trim($dirName, '-');

        return strtolower($dirName);
    }
}
