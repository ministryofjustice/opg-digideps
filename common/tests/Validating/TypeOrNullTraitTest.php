<?php

declare(strict_types=1);

namespace OPG\Digideps\Tests\Common\Validating;

use OPG\Digideps\Common\Validating\ValidatingArray;
use PHPUnit\Framework\TestCase;

class TypeOrNullTraitTest extends TestCase
{
    public function testGetIntegerOrNull(): void
    {
        $array = ['1', 'two' => 2, 'three' => 3.0];
        $validatingArray = new ValidatingArray($array);
        $this->assertNull($validatingArray->getIntegerOrNull(0));
        $this->assertSame(2, $validatingArray->getIntegerOrNull('two'));
        $this->assertNull($validatingArray->getIntegerOrNull('three'));
        $this->assertNull($validatingArray->getIntegerOrNull(5));
    }

    public function testGetFloatOrNull(): void
    {
        $array = ['1', 'two' => 2, 'three' => 3.0];
        $validatingArray = new ValidatingArray($array);
        $this->assertNull($validatingArray->getFloatOrNull(0));
        $this->assertNull($validatingArray->getFloatOrNull('two'));
        $this->assertSame(3.0, $validatingArray->getFloatOrNull('three'));
        $this->assertNull($validatingArray->getFloatOrNull(5));
    }

    public function testGetArrayOrNull(): void
    {
        $test = [1, 2, 3];
        $array = ['1', 'two' => 2, 'three' => $test];
        $validatingArray = new ValidatingArray($array);
        $this->assertNull($validatingArray->getArrayOrNull(0));
        $this->assertNull($validatingArray->getArrayOrNull('two'));
        $this->assertSame($test, $validatingArray->getArrayOrNull('three'));
        $this->assertNull($validatingArray->getArrayOrNull(5));
    }

    public function testGetStringOrNull(): void
    {
        $array = ['1', 'two' => 2, 'three' => 3.0];
        $validatingArray = new ValidatingArray($array);
        $this->assertSame('1', $validatingArray->getStringOrNull(0));
        $this->assertNull($validatingArray->getStringOrNull('two'));
        $this->assertNull($validatingArray->getStringOrNull('three'));
        $this->assertNull($validatingArray->getStringOrNull(5));
    }

    public function testGetObjectOrNull(): void
    {
        $array = [1, 'two' => '2', 'a' => new \DateTime(), 'b' => TypeOrNullTraitTest::class, 'c' => new \DateTimeImmutable(), 'd' => null];
        $validatingArray = new ValidatingArray($array);
        $this->assertNull($validatingArray->getObjectOrNull(0, \DateTime::class));
        $this->assertNull($validatingArray->getObjectOrNull('two', \DateTime::class));
        $this->assertInstanceOf(\DateTime::class, $validatingArray->getObjectOrNull('a', \DateTime::class));
        $this->assertNull($validatingArray->getObjectOrNull('b', TypeOrNullTraitTest::class));
        $this->assertNull($validatingArray->getObjectOrNull('c', \DateTime::class));
        $this->assertNull($validatingArray->getObjectOrNull('d', \DateTime::class));
        $this->assertNull($validatingArray->getObjectOrNull('e', \DateTime::class));
    }
}
