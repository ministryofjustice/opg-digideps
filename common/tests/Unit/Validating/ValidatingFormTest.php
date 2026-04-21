<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Common\Unit\Validating;

use OPG\Digideps\Common\Validating\ValidatingForm;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;

class ValidatingFormTest extends TestCase
{
    public function testGetUnvalidated(): void
    {
        $form = $this->makeFormStub(5, ['a' => 'A', 'b' => 'B']);
        $validatingForm = new ValidatingForm($form);
        $this->assertSame(5, $validatingForm->getIntegerOrNull(null));
        $this->assertSame('A', $validatingForm->getStringOrNull('a'));
        $this->assertSame('B', $validatingForm->getStringOrNull('b'));
        $this->assertNull($validatingForm->getStringOrNull('c'));
    }

    public function testGetValidatingFormOrNull(): void
    {
        $form = $this->getMockBuilder(FormInterface::class)->getMock();
        $form->expects($this->exactly(2))->method('has')->willReturnMap([['a', false], ['b', true]]);
        $form->expects($this->once())->method('get')->with('b')->willReturn($this->createStub(FormInterface::class));
        $validatingForm = new ValidatingForm($form);
        $this->assertNull($validatingForm->getValidatingFormOrNull('a'));
        $this->assertInstanceOf(ValidatingForm::class, $validatingForm->getValidatingFormOrNull('b'));
    }

    private function makeFormStub(mixed $value, array $children = []): FormInterface
    {
        $form = $this->createStub(Form::class);
        $form->method('getData')->willReturn($value);
        $map = [];
        foreach ($children as $key => $value) {
            $map[] = [$key, $this->makeFormStub($value)];
        }
        $form->method('get')->willReturnMap($map);
        $form->method('has')->willReturnCallback(fn(string $key) => array_key_exists($key, $children));
        return $form;
    }
}
