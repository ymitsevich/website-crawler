<?php

namespace App\Test\Integration\WebCrawler\Service;

use App\WebCrawler\Dto\Page;
use App\WebCrawler\Link\LinkNormalizer;
use App\WebCrawler\PageFetcher\PageFetcher;
use App\WebCrawler\PageSaver\PageSaver;
use App\WebCrawler\Service\WebsiteCrawlerService;
use DOMDocument;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WebsiteCrawlerServiceTest extends KernelTestCase
{
    use ProphecyTrait;

    private readonly WebsiteCrawlerService $service;
    private readonly PageFetcher|ObjectProphecy $pageFetcher;
    private readonly PageSaver|ObjectProphecy $pageSaver;
    private readonly LinkNormalizer $linkNormalizer;

    public function setUp(): void
    {
        $this->setEnvVar('APP_LINKS_MAX', 10);

        $this->pageFetcher = $this->prophesize(PageFetcher::class);
        $this->getContainer()->set(PageFetcher::class, $this->pageFetcher->reveal());
        $this->pageSaver = $this->prophesize(PageSaver::class);
        $this->getContainer()->set(PageSaver::class, $this->pageSaver->reveal());
        $this->service = $this->getContainer()->get(WebsiteCrawlerService::class);

        $this->linkNormalizer = $this->getContainer()->get(LinkNormalizer::class);
    }

    /**
     * @dataProvider input
     */
    public function testProcess(array $hierarchy, array $referencingPositive, array $referencingNegative)
    {
        $mainLink = key($hierarchy);

        $this->craftLinksHierarchy($hierarchy);
        $this->pageSaver->save(
            Argument::that(function (Page $page) use ($referencingPositive, $referencingNegative): Page {
                Assert::assertTrue(in_array($page->getName(), $referencingPositive));
                Assert::assertFalse(in_array($page->getName(), $referencingNegative));

                return $page;
            }),
            $mainLink
        );

        $this->service->process($mainLink);
    }

    public function input()
    {
        $mainLink = 'https://test.ua';

        $referencingPositive = [
            $mainLink,
            "/lev1link1",
            "/lev1link2",
            "/lev2link1",
            "/lev2link2",
            "/lev2link3",
            "/lev2link4",
            "/lev2link5",
            "/lev2link6",
            "/lev2link7",
        ];
        $referencingNegative = [
            "/lev2link8",
        ];
        $hierarchy = [
            $mainLink => ['/lev1link1', '/lev1link2'],
            '/lev1link1' => ['/lev2link1', '/lev2link2', '/lev2link3', '/lev2link4', '/lev2link5'],
            '/lev1link2' => ['/lev2link6', '/lev2link7', '/lev2link8'],
            '/lev2link1' => ['/lev3link1', '/lev2link2'],
            '/lev2link2' => ['/lev3link3', '/lev2link4'],
            '/lev2link3' => ['/lev3link5', '/lev2link6'],
            '/lev2link4' => ['/lev3link7', '/lev2link8'],
            '/lev2link5' => ['/lev3link9', '/lev2link10'],
            '/lev2link6' => ['/lev3link11', '/lev2link12'],
            '/lev2link7' => ['/lev3link13', '/lev2link14'],
            '/lev2link8' => ['/lev3link15', '/lev2link16'],
        ];

        return [
            'maxLinksReached_shouldStopCrawling' => [$hierarchy, $referencingPositive, $referencingNegative],
        ];
    }

    public function testProcessWithEmptyHierarchy()
    {
        $mainLink = 'https://test.ua';

        $this->craftLinksHierarchy([$mainLink => []]);
        $this->pageSaver->save(
            Argument::that(function (Page $page) use ($mainLink): Page {
                Assert::assertEquals($page->getName(), $mainLink);

                return $page;
            }),
            $mainLink
        );

        $this->service->process($mainLink);
    }

    private function craftLinksHierarchy(array $urlToContentFileNameMapping): void
    {
        $sampleFile = '/app/tests/samples/sample.html';
        $sampleInitContent = file_get_contents($sampleFile);
        $dom = new DOMDocument();

        $mainLink = key($urlToContentFileNameMapping);
        foreach ($urlToContentFileNameMapping as $targetUrl => $links) {
            @$dom->loadHTML($sampleInitContent);
            $domLinksDiv = $dom->getElementById('links');

            foreach ($links as $link) {
                $domLink = $dom->createElement('a');
                $domLink->setAttribute('href', $link);
                $domLinksDiv->appendChild($domLink);
            }
            $content = $dom->saveHTML();

            $page = (new Page($targetUrl))->setContent($content);
            $targetFullUrl = $this->linkNormalizer->process($targetUrl, $mainLink);
            $this->pageFetcher->getByLink($targetFullUrl)->willReturn($page);
        }
    }

    private function setEnvVar(string $name, string $value): void
    {
        $_ENV[$name] = $value;
    }
}
