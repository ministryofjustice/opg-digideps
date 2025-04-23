<?php

namespace App\Form\Report\Asset;

use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class AssetTypeTitleTest extends TestCase
{
    private TranslatorInterface|MockInterface $translator;

    public function setUp(): void
    {
        $this->translator = m::mock('Symfony\Contracts\Translation\TranslatorInterface');
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
        $object = new AssetTypeTitle($input, $this->translator, 'domain');

        $this->assertEquals($expectedOutput, $object->getTitleChoices());
    }

    public function tearDown(): void
    {
        m::close();
    }
}
