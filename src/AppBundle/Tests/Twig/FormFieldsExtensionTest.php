<?php
namespace AppBundle\Test\Twig;

//use AppBundle\Service\ApiClient;
//use Mockery as m;

class FormFieldsExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->object = new \AppBundle\Twig\FormFieldsExtension;
    }
 
    public static function accordionLinksProvider()
    {
        return [
            ["money-in", "list", ["money-in", "money-out"], "money-both"]
        ];
    }
    
    /**
     * @dataProvider accordionLinksProvider
     */
    public function testRenderAccordionLinks($currentAction, $allClosed, $panels, $allOpen)
    {
        $actual = $this->object->renderAccordionLinks($currentAction, $allClosed, $panels, $allOpen);
        
    }
}