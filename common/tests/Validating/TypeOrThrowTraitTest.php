<?php

declare(strict_types=1);

namespace OPG\Digideps\Tests\Common\Validating;

use OPG\Digideps\Common\Validating\ValidatingArray;
use OPG\Digideps\Common\Validating\ValidationException;
use PHPUnit\Framework\TestCase;

class TypeOrThrowTraitTest extends TestCase
{
    public function testGetIntegerOrThrow()
    {
        $array = ['1', 'two' => 2, 'three' => 3.0];
        $validatingArray = new ValidatingArray($array);
        $this->assertSame(2, $validatingArray->getIntegerOrThrow('two'));
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Expected value of type int. Got: 3.0');
        $validatingArray->getIntegerOrThrow('three');
    }

    public function testGetFloatOrThrow()
    {
        $array = ['1', 'two' => 2, 'three' => 3.0];
        $validatingArray = new ValidatingArray($array);
        $this->assertSame(3.0, $validatingArray->getFloatOrThrow('three'));
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Expected value of type float. Got: NULL');
        $validatingArray->getFloatOrThrow(5);
    }

    public function testGetArrayOrThrow()
    {
        $test = [1, 2, 3];
        $array = ['1', 'two' => 2, 'three' => $test];
        $validatingArray = new ValidatingArray($array);
        $this->assertSame($test, $validatingArray->getArrayOrThrow('three'));
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Expected value of type array. Got: 2');
        $validatingArray->getArrayOrThrow('two');
    }

    public function testGetStringOrThrow()
    {
        $array = ['1', 'two' => 2, 'three' => [3.0, null]];
        $validatingArray = new ValidatingArray($array);
        $this->assertSame('1', $validatingArray->getStringOrThrow(0));
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Expected value of type string. Got: array (\n  0 => 3.0,\n  1 => NULL,\n)");
        $validatingArray->getStringOrThrow('three');
    }

    public function testGetObjectOrThrow()
    {
        $array = ['a' => new \DateTime(), 'b' => TypeOrThrowTraitTest::class];
        $validatingArray = new ValidatingArray($array);
        $this->assertInstanceOf(\DateTime::class, $validatingArray->getObjectOrThrow('a', \DateTime::class));
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Expected value of type OPG\Digideps\Tests\Common\Validating\TypeOrThrowTraitTest. Got: \'OPG\\\\Digideps\\\\Tests\\\\Common\\\\Validating\\\\TypeOrThrowTraitTest\'');
        $validatingArray->getObjectOrThrow('b', TypeOrThrowTraitTest::class);
    }
}
