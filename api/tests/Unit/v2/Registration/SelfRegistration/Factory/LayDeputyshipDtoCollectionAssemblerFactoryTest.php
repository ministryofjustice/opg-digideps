<?php

namespace App\Tests\Unit\v2\Registration\SelfRegistration\Factory;

use App\v2\Registration\Assembler\SiriusToLayDeputyshipDtoAssembler;
use App\v2\Registration\SelfRegistration\Factory\LayDeputyshipDtoCollectionAssemblerFactory;
use PHPUnit\Framework\TestCase;

class LayDeputyshipDtoCollectionAssemblerFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function createsSiriusAssemblerWhenSourceIsSirius()
    {
        $assembler = (new LayDeputyshipDtoCollectionAssemblerFactory())->create([['Source' => 'sirius']]);
        $this->assertInstanceOf(SiriusToLayDeputyshipDtoAssembler::class, $assembler->getLayDeputyshipDtoAssembler());
    }
}
