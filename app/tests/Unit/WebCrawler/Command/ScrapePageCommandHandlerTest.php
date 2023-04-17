<?php

namespace App\Tests\Unit\WebCrawler\Command;

use App\WebCrawler\Command\ScrapePageCommand;
use App\WebCrawler\Command\ScrapePageCommandHandler;
use App\WebCrawler\Dto\Page;
use App\WebCrawler\Exception\PageFetchException;
use App\WebCrawler\Exception\PageSaveException;
use App\WebCrawler\PageFetcher\PageFetcher;
use App\WebCrawler\PageSaver\PageSaver;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class ScrapePageCommandHandlerTest extends TestCase
{
    use ProphecyTrait;

    private readonly ScrapePageCommandHandler $handler;

    private readonly ObjectProphecy|PageFetcher $pageFetcher;

    private readonly ObjectProphecy|PageSaver $pageSaver;

    private readonly ObjectProphecy|LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->pageFetcher = $this->prophesize(PageFetcher::class);
        $this->pageSaver = $this->prophesize(PageSaver::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->handler = new ScrapePageCommandHandler(
            $this->pageFetcher->reveal(),
            $this->pageSaver->reveal(),
            $this->logger->reveal()
        );
    }

    public function testInvoke_pageFetchedAndSaved_success()
    {
        $link = 'https://example.com/page';
        $mainLink = 'https://example.com';
        $page = $this->createMock(Page::class);

        $this->pageFetcher->getByLink($link)
            ->willReturn($page)
            ->shouldBeCalledOnce();

        $this->pageSaver->save($page, $mainLink)
            ->shouldBeCalledOnce();

        $command = new ScrapePageCommand($link, $mainLink);
        $this->handler->__invoke($command);
    }

    public function testInvoke_pageFetchError_logAndReturn()
    {
        $link = 'https://example.com/page';
        $mainLink = 'https://example.com';
        $exceptionMessage = 'Failed to fetch the page content';

        $this->pageFetcher->getByLink($link)
            ->willThrow(new PageFetchException($exceptionMessage))
            ->shouldBeCalledOnce();

        $this->logger->error($exceptionMessage)
            ->shouldBeCalledOnce();

        $this->pageSaver->save(Argument::type(Page::class), $mainLink)
            ->shouldNotBeCalled();

        $command = new ScrapePageCommand($link, $mainLink);
        $this->handler->__invoke($command);
    }

    public function testInvoke_pageSaveError_return()
    {
        $link = 'http://example.com';
        $mainLink = 'http://example.com';

        $page = new Page('dummyPage');
        $this->pageFetcher->getByLink($link)->willReturn($page);
        $this->pageSaver->save($page, $mainLink)->shouldBeCalledOnce()
            ->willThrow(new PageSaveException('save error'));

        $command = new ScrapePageCommand($link, $mainLink);
        $this->handler->__invoke($command);
    }
}
