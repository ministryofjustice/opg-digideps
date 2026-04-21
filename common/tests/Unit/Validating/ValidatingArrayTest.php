<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Common\Unit\Validating;

use OPG\Digideps\Common\Validating\ValidatingArray;
use PHPUnit\Framework\TestCase;

class ValidatingArrayTest extends TestCase
{
    public function testGetValidatingArrayOrNull(): void
    {
        $array = [1, 'two' => '2', [], 'four' => [1, 2, 3]];
        $validatingArray = new ValidatingArray($array);
        $this->assertNull($validatingArray->getValidatingArrayOrNull(0));
        $this->assertNull($validatingArray->getValidatingArrayOrNull('two'));
    }
}
