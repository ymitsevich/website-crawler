<?php

namespace App\Tests\Unit\WebCrawler\Link;

use App\WebCrawler\Link\BreadthFirstLinkSeeker;
use App\WebCrawler\Link\LinkNormalizer;
use App\WebCrawler\Link\Validator;
use App\WebCrawler\PageFetcher\PageManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class BreadthFirstLinkSeekerTest extends TestCase
{
    use ProphecyTrait;

    private readonly BreadthFirstLinkSeeker $service;

    private readonly PageManager|ObjectProphecy $pageManager;
    private readonly Validator|ObjectProphecy $validator;
    private readonly LinkNormalizer|ObjectProphecy $linkNormalizer;

    protected function setUp(): void
    {
        $this->pageManager = $this->prophesize(PageManager::class);
        $this->validator = $this->prophesize(Validator::class);
        $this->linkNormalizer = $this->prophesize(LinkNormalizer::class);
        $this->service = new BreadthFirstLinkSeeker(
            maxLinks: 10,
            pageManager: $this->pageManager->reveal(),
            validator: $this->validator->reveal(),
            linkNormalizer: $this->linkNormalizer->reveal()
        );
    }

    public function testGetByLink_emptyPageManager_returnsMainLink()
    {
        $mainLink = 'http://example.com';
        $this->pageManager->getLinksByLink($mainLink)->willReturn([]);

        $this->validator->isValidLink(Argument::cetera())->shouldNotBeCalled();
        $result = $this->service->getByLink($mainLink);

        $this->assertEquals([$mainLink], $result);
    }

    public function testGetByLink_correct_returnsLinks()
    {
        $mainLink = 'https://example.com';
        $this->pageManager->getLinksByLink($mainLink)->shouldBeCalledOnce()
            ->willReturn([
                'https://example.com/page1',
                'https://example.com/page2',
                'https://example.com/page3',
                'https://example.com/page4',
                'https://example.com/page5',
            ]);
        $this->pageManager->getLinksByLink('https://example.com/page1')->willReturn([]);
        $this->pageManager->getLinksByLink('https://example.com/page2')->willReturn([]);
        $this->pageManager->getLinksByLink('https://example.com/page3')->willReturn([]);
        $this->pageManager->getLinksByLink('https://example.com/page4')->willReturn([]);
        $this->pageManager->getLinksByLink('https://example.com/page5')->willReturn([]);
        $this->linkNormalizer->process(Argument::type('string'), $mainLink)->shouldBeCalledTimes(5)
            ->willReturnArgument(0);
        $this->validator->isValidLink(Argument::any(), $mainLink)->shouldBeCalledTimes(5)->willReturn(true);

        $result = $this->service->getByLink($mainLink);

        $this->assertEquals(
            [
                $mainLink,
                'https://example.com/page1',
                'https://example.com/page2',
                'https://example.com/page3',
                'https://example.com/page4',
                'https://example.com/page5',
            ],
            $result
        );
    }

    public function testGetByLink_withDuplicateLinks_compact()
    {
        $this->pageManager->getLinksByLink('https://example.com')->willReturn(
            ['https://example.com/page', 'https://example.com/page']
        );
        $this->pageManager->getLinksByLink('https://example.com/page')->willReturn([]);
        $this->validator->isValidLink('https://example.com/page', 'https://example.com')->willReturn(true);
        $this->linkNormalizer->process('https://example.com/page', 'https://example.com')->willReturn(
            'https://example.com/page'
        );

        $result = $this->service->getByLink('https://example.com');

        $this->assertEquals(['https://example.com', 'https://example.com/page'], $result);
    }
}
