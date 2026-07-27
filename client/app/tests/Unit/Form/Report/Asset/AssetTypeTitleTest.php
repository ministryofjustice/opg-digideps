<?php

namespace Tests\OPG\Digideps\Frontend\Unit\Form\Report\Asset;

use OPG\Digideps\Frontend\Form\Report\Asset\AssetTypeTitle;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class AssetTypeTitleTest extends TestCase
{
    private TranslatorInterface&MockObject $translator;

    public function setUp(): void
    {
        $this->translator = $this->createMock('Symfony\Contracts\Translation\TranslatorInterface');
        $this->translator->method('trans')->with(new IsType(IsType::TYPE_STRING), [], 'domain')->willReturnCallback(fn (string $a) => "{$a}-TRANSLATED");
    }

    public function titleChoices(): array
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
    public function testGetTitleChoices(array $input, array $expectedOutput): void
    {
        $object = new AssetTypeTitle($input, $this->translator, 'domain');

        $this->assertEquals($expectedOutput, $object->getTitleChoices());
    }
}
