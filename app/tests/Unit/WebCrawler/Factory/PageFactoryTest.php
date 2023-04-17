<?php

namespace App\Tests\Unit\WebCrawler\Factory;

use App\Common\RandomDataGenerator;
use App\WebCrawler\Dto\Page;
use App\WebCrawler\Factory\PageFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class PageFactoryTest extends TestCase
{
    use ProphecyTrait;

    private readonly PageFactory $service;

    private readonly RandomDataGenerator|ObjectProphecy $randomDataGenerator;

    protected function setUp(): void
    {
        $this->randomDataGenerator = $this->prophesize(RandomDataGenerator::class);
        $this->service = new PageFactory($this->randomDataGenerator->reveal());
    }

    public function testCreateByContent_noTitleOrH1_returnsPageWithRandomName()
    {
        $content = '<html><body><div>Some content</div></body></html>';
        $this->randomDataGenerator->createUniqId()->willReturn('random_uuid');

        $page = $this->service->createByContent($content);

        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('random_uuid', $page->getName());
        $this->assertEquals($content, $page->getContent());
    }

    public function testCreateByContent_titleAndH1_returnsPageWithNameContainingBoth()
    {
        $content = '<html><head><title>Page Title</title></head><body><h1>Page Header</h1><div>Some content</div></body></html>';
        $this->randomDataGenerator->createUniqId()->willReturn('random_uuid');

        $page = $this->service->createByContent($content);

        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('Page Title_Page Header_random_uuid', $page->getName());
        $this->assertEquals($content, $page->getContent());
    }
}
