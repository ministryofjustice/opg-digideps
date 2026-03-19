<?php

declare(strict_types=1);

namespace OPG\Digideps\Tests\Common\Validating;

use OPG\Digideps\Common\Validating\ValidatingArray;
use PHPUnit\Framework\TestCase;

class A
{
}

class B
{
}

class C
{
}

class TypeOrNullTraitTest extends TestCase
{
    public function testGetIntegerOrNull()
    {
        $array = ['1', 'two' => 2, 'three' => 3.0];
        $validatingArray = new ValidatingArray($array);
        $this->assertNull($validatingArray->getIntegerOrNull(0));
        $this->assertSame(2, $validatingArray->getIntegerOrNull('two'));
        $this->assertNull($validatingArray->getIntegerOrNull('three'));
        $this->assertNull($validatingArray->getIntegerOrNull(5));
    }

    public function testGetFloatOrNull()
    {
        $array = ['1', 'two' => 2, 'three' => 3.0];
        $validatingArray = new ValidatingArray($array);
        $this->assertNull($validatingArray->getFloatOrNull(0));
        $this->assertNull($validatingArray->getFloatOrNull('two'));
        $this->assertSame(3.0, $validatingArray->getFloatOrNull('three'));
        $this->assertNull($validatingArray->getFloatOrNull(5));
    }

    public function testGetArrayOrNull()
    {
        $test = [1, 2, 3];
        $array = ['1', 'two' => 2, 'three' => $test];
        $validatingArray = new ValidatingArray($array);
        $this->assertNull($validatingArray->getArrayOrNull(0));
        $this->assertNull($validatingArray->getArrayOrNull('two'));
        $this->assertSame($test, $validatingArray->getArrayOrNull('three'));
        $this->assertNull($validatingArray->getArrayOrNull(5));
    }

    public function testGetStringOrNull()
    {
        $array = ['1', 'two' => 2, 'three' => 3.0];
        $validatingArray = new ValidatingArray($array);
        $this->assertSame('1', $validatingArray->getStringOrNull(0));
        $this->assertNull($validatingArray->getStringOrNull('two'));
        $this->assertNull($validatingArray->getStringOrNull('three'));
        $this->assertNull($validatingArray->getStringOrNull(5));
    }

    public function testGetObjectOrNull()
    {
        $array = [1, 'two' => '2', 'a' => new A(), 'b' => B::class, 'c' => new C(), 'd' => null];
        $validatingArray = new ValidatingArray($array);
        $this->assertNull($validatingArray->getObjectOrNull(0, A::class));
        $this->assertNull($validatingArray->getObjectOrNull('two', A::class));
        $this->assertInstanceOf(A::class, $validatingArray->getObjectOrNull('a', A::class));
        $this->assertNull($validatingArray->getObjectOrNull('b', B::class));
        $this->assertNull($validatingArray->getObjectOrNull('c', A::class));
        $this->assertNull($validatingArray->getObjectOrNull('d', A::class));
        $this->assertNull($validatingArray->getObjectOrNull('e', A::class));
    }
}
