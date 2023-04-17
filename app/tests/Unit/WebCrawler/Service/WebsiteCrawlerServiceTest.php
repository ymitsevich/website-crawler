<?php

namespace App\Tests\Unit\WebCrawler\Service;

use App\WebCrawler\Command\ScrapePageCommand;
use App\WebCrawler\Exception\ValidationException;
use App\WebCrawler\Link\LinkSeeker;
use App\WebCrawler\Link\Validator;
use App\WebCrawler\Service\WebsiteCrawlerService;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class WebsiteCrawlerServiceTest extends TestCase
{
    use ProphecyTrait;

    private readonly LinkSeeker|ObjectProphecy $linkSeeker;
    private readonly Validator|ObjectProphecy $validator;
    private readonly MessageBusInterface|ObjectProphecy $bus;
    private readonly LoggerInterface|ObjectProphecy $logger;
    private readonly WebsiteCrawlerService $service;

    protected function setUp(): void
    {
        $this->linkSeeker = $this->prophesize(LinkSeeker::class);
        $this->validator = $this->prophesize(Validator::class);
        $this->bus = $this->prophesize(MessageBusInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->service = new WebsiteCrawlerService(
            $this->linkSeeker->reveal(),
            $this->validator->reveal(),
            $this->bus->reveal(),
            $this->logger->reveal()
        );
    }

    public function testProcess_validLink_dispatchesScrapePageCommandForEachLink()
    {
        $mainLink = 'https://example.com';
        $this->validator->isValidLink($mainLink)->willReturn(true);

        $links = ['https://example.com/page1', 'https://example.com/page2'];
        $this->linkSeeker->getByLink($mainLink)->willReturn($links);

        $this->bus->dispatch(new ScrapePageCommand($links[0], $mainLink))->shouldBeCalled()
            ->willReturn(new Envelope(new stdClass()));
        $this->bus->dispatch(new ScrapePageCommand($links[1], $mainLink))->shouldBeCalled()
            ->willReturn(new Envelope(new stdClass()));

        $this->service->process($mainLink);
    }

    public function testProcess_invalidLink_throwsValidationExceptionAndLogsError()
    {
        $mainLink = 'invalid_link';
        $this->validator->isValidLink($mainLink)->willReturn(false);

        $this->logger->error("Invalid main link. [$mainLink]")->shouldBeCalled();

        $this->expectException(ValidationException::class);

        $this->service->process($mainLink);
    }
}
