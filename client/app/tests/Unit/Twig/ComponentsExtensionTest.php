<?php

namespace Tests\OPG\Digideps\Frontend\Unit\Twig;

use OPG\Digideps\Frontend\Service\ReportSectionsLinkService;
use OPG\Digideps\Frontend\Twig\ComponentsExtension;
use Dom\HTMLDocument;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ComponentsExtensionTest extends TestCase
{
    private MockObject&TranslatorInterface $translator;
    private MockObject&ReportSectionsLinkService $reportSectionsLinkService;
    private ComponentsExtension $object;

    public function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->reportSectionsLinkService = $this->createMock(ReportSectionsLinkService::class);
        $this->object = new ComponentsExtension($this->translator, $this->reportSectionsLinkService);
    }

    /**
     * @return array<array{string, bool, bool, string, string, bool}>
     */
    public static function accordionLinksProvider(): array
    {
        return [
            ['list', false, false, 'money-in', 'money-out', false],
            ['money-in', true, false, 'list', 'money-both', false],
            ['money-out', false, true, 'money-both', 'list', false],
            ['money-both', true, true, 'money-out', 'money-in', false],
            // oneATime
            ['list', false, false, 'money-in', 'money-out', true],
            ['money-in', true, false, 'list', 'money-out', true],
            ['money-out', false, true, 'money-in', 'list', true],
            ['money-both', true, true, 'money-out', 'money-in', true],
        ];
    }

    /**
     * @dataProvider accordionLinksProvider
     */
    public function testRenderAccordionLinks($clickedPanel, $open1, $open2, $href1, $href2, $oneATime)
    {
        $options = ['clickedPanel' => $clickedPanel,
            'bothOpenHref' => 'money-both',
            'allClosedHref' => 'list',
            'firstPanelHref' => 'money-in',
            'secondPanelHref' => 'money-out',
            'onlyOneATime' => $oneATime, ];

        $expected = [ // expected
            'first' => [
                'open' => $open1,
                'href' => $href1,
            ],
            'second' => [
                'open' => $open2,
                'href' => $href2,
            ],
        ];

        $actual = $this->object->renderAccordionLinks($options);
        $this->assertEquals($expected, $actual);
    }

    /**
     * expected results (time diff from 2015-01-29 17:10:00).
     *
     * @return array<array{string, string, array{string, array<string, int|string>, string}}>
     */
    public static function formatLastLoginProvider(): array
    {
        return [
            ['2015-01-29 17:09:30', 'trans', ['PREFIXlessThenAMinuteAgo', [], 'DOMAIN']],

            ['2015-01-29 17:09:00', 'trans', ['PREFIXminutesAgo', ['%count%' => 1], 'DOMAIN']],
            ['2015-01-29 17:07:00', 'trans', ['PREFIXminutesAgo', ['%count%' => 3], 'DOMAIN']],

            ['2015-01-29 16:10:00', 'trans', ['PREFIXhoursAgo', ['%count%' => 1], 'DOMAIN']],
            ['2015-01-29 7:11:00', 'trans', ['PREFIXhoursAgo', ['%count%' => 10], 'DOMAIN']],

            ['2015-01-28 15:10:00', 'trans', ['PREFIXexactDate', ['%date%' => '28 January 2015'], 'DOMAIN']],
        ];
    }

    /**
     * @dataProvider formatLastLoginProvider
     *
     * @param array{string, array<string, int>, string} $methodArgs
     */
    public function testFormatTimeDifference(string $input, string $expectedMethodCalled, array $methodArgs): void
    {
        $this->translator->expects($this->once())->method($expectedMethodCalled)->with(...$methodArgs);

        $this->object->formatTimeDifference([
            'from' => new \DateTime($input),
            'to' => new \DateTime('2015-01-29 17:10:00'),
            'translationDomain' => 'DOMAIN',
            'translationPrefix' => 'PREFIX',
            'defaultDateFormat' => 'd F Y',
        ]);
    }

    public static function padDayMonthProvider(): array
    {
        return [
            ['a', 'a'],
            [null, null],
            [0, 0],
            ['0', '0'],

            [1, '01'],
            ['1', '01'],
            ['01', '01'],
            ['00000001', '01'],
            [9, '09'],
            ['09', '09'],

            ['10', '10'],
            ['31', '31'],
        ];
    }

    /**
     * @dataProvider padDayMonthProvider
     */
    public function testPadDayMonth(int|string|null $input, int|string|null $expected): void
    {
        $f = $this->object->getFilters()['pad_day_month']->getCallable();

        $this->assertSame($expected, $f($input));
    }

    public static function behatNamifyProvider(): array
    {
        return [
            ['a', 'a'],
            ['  ab_cd-1  23  ! "random    data"!@£$%^&end*    ', 'ab-cd-1-23-random-dataend'],
            ['Alfa Romeo 156 JTD', 'alfa-romeo-156-jtd'],
        ];
    }

    /**
     * @dataProvider behatNamifyProvider
     */
    public function testBehatNamify(string $input, string $expected): void
    {
        $f = $this->object->getFilters()['behat_namify']->getCallable();

        $this->assertSame($expected, $f($input));
    }

    public static function moneyFormatProvider(): array
    {
        return [
            ['0', '0.00'],
            ['1000', '1,000.00'],
            ['123456.1', '123,456.10'],
        ];
    }

    /**
     * @dataProvider moneyFormatProvider
     */
    public function testMoneyFormat(string|int|null $input, string|int|null $expected): void
    {
        $f = $this->object->getFilters()['money_format']->getCallable();

        $this->assertSame($expected, $f($input));
    }

    public function testClassName(): void
    {
        $f = $this->object->getFilters()['class_name']->getCallable();

        $this->assertEquals(null, $f(0));
        $this->assertEquals(null, $f([]));
        $this->assertEquals(null, $f(''));
        $this->assertEquals('Closure', $f(function () {
        }));
        $this->assertEquals('DateTime', $f(new \DateTime()));
    }

    public function testLcfirst(): void
    {
        $f = $this->object->getFilters()['lcfirst']->getCallable();

        $this->assertNull($f(null));
        $this->assertEquals('', $f(''));
        $this->assertEquals('123aBc', $f('123aBc'));
        $this->assertEquals('aBCd', $f('ABCd'));
        $this->assertEquals('assets held outside England and Wales', $f('Assets held outside England and Wales'));
    }

    public function testProgressBarReportSubmission(): void
    {
        $loader = new FilesystemLoader(__DIR__ . '/../../../templates');
        $loader->addPath(__DIR__ . '/../../../templates/', 'App');

        $env = new Environment($loader);
        $env->addExtension(new TranslationExtension($this->translator));
        $env->addExtension($this->object);

        // Add expectations for the trans() calls made by the progress indicator template
        $this->translator->expects($this->exactly(3))->method('trans')
            ->willReturnMap([
                ['reportSubmissionProgressBar.review_report.label', [], 'common', null, 'Review report'],
                ['reportSubmissionProgressBar.report_confirm_details.label', [], 'common', null, 'Confirm details'],
                ['reportSubmissionProgressBar.report_declaration.label', [], 'common', null, 'Declaration'],
            ]);

        ob_start();
        $this->object->progressBarReportSubmission($env, 'report_confirm_details');
        $html = ob_get_contents();
        ob_end_clean();

        $doc = HTMLDocument::createFromString(
            '<!DOCTYPE html><html lang="en"><head></head><body>' . $html . '</body></html>'
        );

        $selector = 'li.opg-progress-bar__item';
        foreach ($doc->querySelectorAll($selector) as $pos => $liNode) {
            [$expectedStepText, $expectedStatus, $expectedClasses] = match ($pos) {
                0 => ['Review report', '- completed', ['opg-progress-bar__item--completed', 'opg-progress-bar__item--previous']],
                1 => ['Confirm details', '- current step', ['opg-progress-bar__item--active']],
                2 => ['Declaration', '- incomplete', ['opg-progress-bar__item--incomplete']],
                default => throw new \LogicException('Unexpected list item position'),
            };

            $this->assertStringContainsString($expectedStepText, $liNode->textContent);
            foreach ($expectedClasses as $expectedClass) {
                $this->assertStringContainsString($expectedClass, $liNode->getAttribute('class'));
            }

            $visuallyHiddenContent = $liNode->querySelector('.govuk-visually-hidden')->textContent;

            $this->assertStringContainsString($expectedStatus, $visuallyHiddenContent);
        }
    }
}
