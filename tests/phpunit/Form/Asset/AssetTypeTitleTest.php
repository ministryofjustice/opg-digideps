<?php

namespace AppBundle\Form\Asset;

use Mockery as m;

class AssetTypeTitleTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->translator = m::mock('Symfony\Component\Translation\TranslatorInterface');
        $this->translator->shouldReceive('trans')->with(m::any(), [], 'domain')->andReturnUsing(function ($a) { return $a.'-TRANSLATED';});
    }

    public function titleChoices()
    {
        return [
            [[], []],
            [['test'], ['dropdown.test-TRANSLATED' => 'dropdown.test-TRANSLATED']],
            [['c', 'b', 'a'], ['dropdown.b-TRANSLATED' => 'dropdown.b-TRANSLATED', 'dropdown.c-TRANSLATED' => 'dropdown.c-TRANSLATED', 'dropdown.a-TRANSLATED' => 'dropdown.a-TRANSLATED']],
        ];
    }

    /**
     * @dataProvider titleChoices
     */
    public function testgetTitleChoices($input, $expectedOutput)
    {
        $this->object = new AssetTypeTitle($input, $this->translator, 'domain');

        $this->assertEquals($expectedOutput,  $this->object->getTitleChoices());
    }

    public function tearDown()
    {
        m::close();
    }
}
