<?php

namespace AppBundle\Resources\views\Report;

use Mockery as m;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\RouterInterface;

class PaginatorTest extends WebTestCase
{
    /**
     * @var Crawler
     */
    protected $crawler;
    protected $twig;

    public function setUp(): void
    {
        $this->mockRouter = m::mock(RouterInterface::class);
        $this->mockRouter->shouldReceive('generate')->with('route', m::any(), 1)->andReturnUsing(function ($a, $b) {
            return $a . '/' . http_build_query($b);
        });

        $this->frameworkBundleClient = static::createClient(['environment' => 'test', 'debug' => true]);
        $container = $this->frameworkBundleClient->getContainer();
        $container->set('router', $this->mockRouter);

        $this->twig = $this->frameworkBundleClient->getContainer()->get('templating');
    }

    private function html($params)
    {
        return $this->twig->render('AppBundle:Components:paginator.html.twig', $params + [
                'messages'       => [
                    'singlePage' => '{0} Showing 0 records|{1} Showing 1 record|]1,Inf[ Showing %count% records',
                    'multiPage'  => 'Showing %from% - %to% of %total% records',
                ],
                'recordsPerPage' => 15,
                'routeName'      => 'route',
                'routeParams'    => ['a' => 'b'],
            ]);
    }

    public static function singlePageProvider()
    {
        return [
            [0, 'Showing 0 records'],
            [1, 'Showing 1 record'],
            [2, 'Showing 2 records'],
            [15, 'Showing 15 records'],
        ];
    }

    /**
     * @dataProvider singlePageProvider
     */
    public function testSinglePage($totalRecords, $expectedText)
    {
        $html = $this->html([
            'totalRecords'   => $totalRecords,
            'recordsPerPage' => 15,
        ]);

        $this->assertStringContainsString($expectedText, $html);
        $this->assertStringNotContainsString('Prev', $html);
        $this->assertStringNotContainsString('Next', $html);
    }

    public static function multiPageProvider()
    {
        return [
            // page 1 of 3
            [0, 30, 'Showing 1 - 15 of 30 records', [
                'prev' => null,
                '1' => null,
                '2' => 'route/a=b&offset=15',
                'next' => 'route/a=b&offset=15']],
            // page 2 of 2
            [15, 30, 'Showing 16 - 30 of 30 records', [
                'prev' => 'route/a=b&offset=0',
                '1' => 'route/a=b&offset=0',
                '2' => null,
                'next' => null]],
                // page 2 of 2
            [0, 0, 'Showing 0 records', [
                'prev' => null,
                'next' => null]],
            [0, 1, 'Showing 1 record', [
                'prev' => null,
                'next' => null]],
        ];
    }

    /**
     * @dataProvider multiPageProvider
     */
    public function testMultiPage($currentOffset, $totalRecords, $expectedText, $expectedLink)
    {
        $html = $this->html([
            'currentOffset'  => $currentOffset,
            'totalRecords'   => $totalRecords,
            'recordsPerPage' => 15,
        ]);
        $crawler = new Crawler($html);

        // find links
        $actualLinks = [
            'prev' => $crawler->filter('[data-test-id="pager-prev"]')->count() ? $crawler->filter('[data-test-id="pager-prev"]')->eq(0)->attr('href') : null,
            'next' => $crawler->filter('[data-test-id="pager-next"]')->count() ? $crawler->filter('[data-test-id="pager-next"]')->eq(0)->attr('href') : null,
        ];
        $crawler->filter('.opg-pager__item')->each(function ($li) use (&$actualLinks) {
            if ($li->filter('a')->count()) {
                $a = $li->filter('a')->eq(0);
                $actualLinks[$a->html()] = $a->attr('href');
            } else {
                $actualLinks[$li->html()] = null;
            }
        });
        ksort($expectedLink);
        ksort($actualLinks);

        $this->assertEquals($expectedLink, $actualLinks);
        $this->assertStringContainsString($expectedText, $crawler->filter('[data-test-id="pager-summary"]')->eq(0)->html());
    }
}
