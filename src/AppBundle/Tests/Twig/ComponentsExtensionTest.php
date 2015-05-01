<?php
namespace AppBundle\Test\Twig;

//use AppBundle\Service\ApiClient;
use Mockery as m;

class ComponentsExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->translator = m::mock('Symfony\Component\Translation\TranslatorInterface');
        $this->object = new \AppBundle\Twig\ComponentsExtension($this->translator, []);
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
                    'secondPanelHref'=>'money-out',
                    'onlyOneATime' => $oneATime];
        
        $expected = [ //expected
            'first'=>[
                'open'=> $open1, 
                'href'=> $href1
             ], 
            'second'=>[
                'open'=> $open2, 
                'href'=> $href2
             ]
        ];
            
        $actual = $this->object->renderAccordionLinks($options);
        $this->assertEquals($expected, $actual);
        
    }
    
    /**
     * expected results (time diff from 2015-01-29 17:10:00)
     */
    public static function formatLastLogiProvider()
    {
        return [
            ['2015-01-29 17:09:30', 'less than a minute ago'],
            
            ['2015-01-29 17:09:00', '1 minute ago'],
            ['2015-01-29 17:07:00', '3 minutes ago'],
            
            ['2015-01-29 16:10:00', '1 hour ago'],
            ['2015-01-29 7:11:00', '10 hours ago'],
            
            ['2015-01-28 15:10:00', '28 January 2015'],
        ];
    }
    
    /**
     * @test
     * @dataProvider formatLastLogiProvider
     */
    public function formatTimeDifference($input, $expected)
    {
        $date = new \DateTime($input);
        $actual =  $this->object->formatTimeDifference($date, new \DateTime('2015-01-29 17:10:00'));
        $this->assertEquals($expected, $actual);
    }
}