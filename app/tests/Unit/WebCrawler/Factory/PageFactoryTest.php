<?php

namespace App\Tests\Unit\WebCrawler\Factory;

use App\Common\RandomDataGenerator;
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

    public function testCreateByContent()
    {
        $content = 'dummyContent';
        $assertingResult = $this->service->createByContent($content);

        $this->assertSame('asdasd', $assertingResult);
    }
}
