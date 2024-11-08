<?php

namespace App\Tests\Unit\v2\Registration\SelfRegistration\Factory;

use App\v2\Registration\Assembler\SiriusToLayPreRegistrationDtoAssembler;
use App\v2\Registration\SelfRegistration\Factory\LayPreRegistrationDtoCollectionAssemblerFactory;
use PHPUnit\Framework\TestCase;

class LayPreRegistrationDtoCollectionAssemblerFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function createsSiriusAssemblerWhenSourceIsSirius()
    {
        $assembler = (new LayPreRegistrationDtoCollectionAssemblerFactory())->create([['Source' => 'sirius']]);
        $this->assertInstanceOf(SiriusToLayPreRegistrationDtoAssembler::class, $assembler->getLayPreRegistrationDtoAssembler());
    }
}
