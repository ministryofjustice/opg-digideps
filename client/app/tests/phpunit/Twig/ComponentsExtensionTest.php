<?php

namespace App\Twig;

use App\Entity\User;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ComponentsExtensionTest extends TestCase
{
    public function setUp(): void
    {
        $this->translator = m::mock('Symfony\Contracts\Translation\TranslatorInterface');
        $this->reportSectionsLinkService = m::mock('App\Service\ReportSectionsLinkService');
        $this->twigEnvironment = m::mock('Twig\Environment');
        $this->object = new ComponentsExtension($this->translator, $this->reportSectionsLinkService, $this->twigEnvironment);
    }

    public static function accordionLinksProvider()
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
     */
    public static function formatLastLoginProvider()
    {
        return [
            ['2015-01-29 17:09:30', 'trans', ['PREFIXlessThenAMinuteAgo', [], 'DOMAIN']],

            ['2015-01-29 17:09:00', 'transChoice', ['PREFIXminutesAgo', 1, ['%count%' => 1], 'DOMAIN']],
            ['2015-01-29 17:07:00', 'transChoice', ['PREFIXminutesAgo', 3, ['%count%' => 3], 'DOMAIN']],

            ['2015-01-29 16:10:00', 'transChoice', ['PREFIXhoursAgo', 1, ['%count%' => 1], 'DOMAIN']],
            ['2015-01-29 7:11:00', 'transChoice', ['PREFIXhoursAgo', 10, ['%count%' => 10], 'DOMAIN']],

            ['2015-01-28 15:10:00', 'trans', ['PREFIXexactDate', ['%date%' => '28 January 2015'], 'DOMAIN']],
        ];
    }

    /**
     * @test
     *
     * @dataProvider formatLastLoginProvider
     *
     * @doesNotPerformAssertions
     */
    public function formatTimeDifference($input, $expectedMethodCalled, $methodArgs)
    {
        if (isset($methodArgs[3])) {
            $this->translator->shouldReceive($expectedMethodCalled)->with($methodArgs[0], $methodArgs[1], $methodArgs[2], $methodArgs[3])->once();
        } else {
            $this->translator->shouldReceive($expectedMethodCalled)->with($methodArgs[0], $methodArgs[1], $methodArgs[2])->once();
        }

        $this->object->formatTimeDifference([
            'from' => new \DateTime($input),
            'to' => new \DateTime('2015-01-29 17:10:00'),
            'translationDomain' => 'DOMAIN',
            'translationPrefix' => 'PREFIX',
            'defaultDateFormat' => 'd F Y',
        ]);

        m::close();
    }

    public static function pad_day_monthProvider()
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
     * @test
     *
     * @dataProvider pad_day_monthProvider
     */
    public function padDayMonth($input, $expected)
    {
        $f = $this->object->getFilters()['pad_day_month']->getCallable();

        $this->assertSame($expected, $f($input));
    }

    public static function behat_namifyProvider()
    {
        return [
            ['a', 'a'],

            ['  ab_cd-1  23  ! "random    data"!@£$%^&end*    ', 'ab-cd-1-23-random-dataend'],
            ['Alfa Romeo 156 JTD', 'alfa-romeo-156-jtd'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider behat_namifyProvider
     */
    public function behatNamify($input, $expected)
    {
        $f = $this->object->getFilters()['behat_namify']->getCallable();

        $this->assertSame($expected, $f($input));
    }

    public static function money_formatProvider()
    {
        return [
            ['0', '0.00'],
            ['1000', '1,000.00'],
            ['123456.1', '123,456.10'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider money_formatProvider
     */
    public function moneyFormat($input, $expected)
    {
        $f = $this->object->getFilters()['money_format']->getCallable();

        $this->assertSame($expected, $f($input));
    }

    public static function progressBarRegistrationProvider()
    {
        $user = m::mock(User::class);
        $user->shouldReceive('getRoleName')->andReturn('ROLE_ADMIN');

        return [
            [],
        ];
    }

    /**
     * @test
     */
    public function className()
    {
        $f = $this->object->getFilters()['class_name']->getCallable();

        $this->assertEquals(null, $f(0));
        $this->assertEquals(null, $f([]));
        $this->assertEquals(null, $f(''));
        $this->assertEquals('Closure', $f(function () {
        }));
        $this->assertEquals('DateTime', $f(new \DateTime()));
    }

    /**
     * @test
     */
    public function lcfirst()
    {
        $f = $this->object->getFilters()['lcfirst']->getCallable();

        $this->assertNull($f(null));
        $this->assertEquals('', $f(''));
        $this->assertEquals('123aBc', $f('123aBc'));
        $this->assertEquals('aBCd', $f('ABCd'));
        $this->assertEquals('assets held outside England and Wales', $f('Assets held outside England and Wales'));
    }
}
