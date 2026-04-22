<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Common\Unit\Validating;

use OPG\Digideps\Common\Validating\ValidatingArray;
use PHPUnit\Framework\TestCase;

class TypeOrDefaultTraitTest extends TestCase
{
    public function testGetIntegerOrDefault(): void
    {
        $array = ['1', 'two' => 2, 'three' => 3.0];
        $default = 99;
        $validatingArray = new ValidatingArray($array);
        $this->assertSame($default, $validatingArray->getIntegerOrDefault(0, $default));
        $this->assertSame(2, $validatingArray->getIntegerOrDefault('two', $default));
        $this->assertSame($default, $validatingArray->getIntegerOrDefault('three', $default));
        $this->assertSame($default, $validatingArray->getIntegerOrDefault(5, $default));
    }

    public function testGetFloatOrDefault(): void
    {
        $default = 55.0;
        $array = ['1', 'two' => 2, 'three' => 3.0];
        $validatingArray = new ValidatingArray($array);
        $this->assertSame($default, $validatingArray->getFloatOrDefault(0, $default));
        $this->assertSame($default, $validatingArray->getFloatOrDefault('two', $default));
        $this->assertSame(3.0, $validatingArray->getFloatOrDefault('three', $default));
        $this->assertSame($default, $validatingArray->getFloatOrDefault(5, $default));
    }

    public function testGetStringOrDefault(): void
    {
        $default = 'DEFAULT';
        $array = ['1', 'two' => 2, 'three' => 3.0];
        $validatingArray = new ValidatingArray($array);
        $this->assertSame('1', $validatingArray->getStringOrDefault(0, $default));
        $this->assertSame($default, $validatingArray->getStringOrDefault('two', $default));
        $this->assertSame($default, $validatingArray->getStringOrDefault('three', $default));
        $this->assertSame($default, $validatingArray->getStringOrDefault(5, $default));
    }

    public function testGetArrayOrDefault(): void
    {
        $default = ["88", "99"];
        $test = [1, 2, 3];
        $array = ['1', 'two' => 2, 'three' => $test];
        $validatingArray = new ValidatingArray($array);
        $this->assertSame($default, $validatingArray->getArrayOrDefault(0, $default));
        $this->assertSame($default, $validatingArray->getArrayOrDefault('two', $default));
        $this->assertSame($test, $validatingArray->getArrayOrDefault('three', $default));
        $this->assertSame($default, $validatingArray->getArrayOrDefault(5, $default));
    }
}
