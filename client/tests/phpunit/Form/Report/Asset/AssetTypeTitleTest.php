<?php

namespace App\Form\Report\Asset;

use Mockery as m;
use PHPUnit\Framework\TestCase;

class AssetTypeTitleTest extends TestCase
{
    public function setUp(): void
    {
        $this->translator = m::mock('Symfony\Component\Translation\TranslatorInterface');
        $this->translator->shouldReceive('trans')->with(m::any(), [], 'domain')->andReturnUsing(function ($a) {
            return $a . '-TRANSLATED';
        });
    }

    public function titleChoices()
    {
        return [
            [[], []],
            [['test'], ['form.title.choices.test-TRANSLATED' => 'form.title.choices.test-TRANSLATED']],
            [['c', 'b', 'a'], ['form.title.choices.b-TRANSLATED' => 'form.title.choices.b-TRANSLATED', 'form.title.choices.c-TRANSLATED' => 'form.title.choices.c-TRANSLATED', 'form.title.choices.a-TRANSLATED' => 'form.title.choices.a-TRANSLATED']],
        ];
    }

    /**
     * @dataProvider titleChoices
     */
    public function testgetTitleChoices($input, $expectedOutput)
    {
        $this->object = new AssetTypeTitle($input, $this->translator, 'domain');

        $this->assertEquals($expectedOutput, $this->object->getTitleChoices());
    }

    public function tearDown(): void
    {
        m::close();
    }
}
