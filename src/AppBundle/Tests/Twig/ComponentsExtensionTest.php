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
}