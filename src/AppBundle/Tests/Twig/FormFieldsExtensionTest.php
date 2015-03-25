<?php
namespace AppBundle\Test\Twig;

//use AppBundle\Service\ApiClient;
use Mockery as m;

class FormFieldsExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->translator = m::mock('Symfony\Component\Translation\TranslatorInterface');
        $this->object = new \AppBundle\Twig\FormFieldsExtension($this->translator, []);
    }
 
    public static function accordionLinksProvider()
    {
        return [
            ['list', false, false, 'money-in', 'money-out'],
            ['money-in', true, false, 'list', 'money-both'],
            ['money-out', false, true, 'money-both', 'list'],
            ['money-both', true, true, 'money-out', 'money-in'],
        ];
    }
    
    /**
     * @dataProvider accordionLinksProvider
     */
    public function testRenderAccordionLinks($clickedPanel, $open1, $open2, $href1, $href2)
    {
        $options = ['clickedPanel' => $clickedPanel, 
                    'bothOpenHref' => 'money-both', 
                    'allClosedHref' => 'list',
                    'firstPanelHref' => 'money-in',
                    'secondPanelHref'=>'money-out'];
        
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