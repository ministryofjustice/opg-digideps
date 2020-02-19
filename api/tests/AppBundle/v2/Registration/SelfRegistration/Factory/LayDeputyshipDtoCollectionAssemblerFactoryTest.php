<?php

namespace Tests\AppBundle\v2\Registration\SelfRegistration\Factory;

use AppBundle\v2\Registration\Assembler\CasRecToLayDeputyshipDtoAssembler;
use AppBundle\v2\Registration\Assembler\SiriusToLayDeputyshipDtoAssembler;
use AppBundle\v2\Registration\SelfRegistration\Factory\LayDeputyshipDtoCollectionAssemblerFactory;
use PHPUnit\Framework\TestCase;

class LayDeputyshipDtoCollectionAssemblerFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function createsCasRecAssemblerWhenSourceIsNotSet()
    {
        $assembler = (new LayDeputyshipDtoCollectionAssemblerFactory())->create([['not-source' => 'foo']]);
        $this->assertInstanceOf(CasRecToLayDeputyshipDtoAssembler::class, $assembler->getLayDeputyshipDtoAssembler());
    }

    /**
     * @test
     */
    public function createsCasRecAssemblerWhenSourceIsNotValid()
    {
        $assembler = (new LayDeputyshipDtoCollectionAssemblerFactory())->create([['source' => 'invalid']]);
        $this->assertInstanceOf(CasRecToLayDeputyshipDtoAssembler::class, $assembler->getLayDeputyshipDtoAssembler());
    }

    /**
     * @test
     */
    public function createsCasRecAssemblerWhenSourceIsCasRec()
    {
        $assembler = (new LayDeputyshipDtoCollectionAssemblerFactory())->create([['source' => 'casrec']]);
        $this->assertInstanceOf(CasRecToLayDeputyshipDtoAssembler::class, $assembler->getLayDeputyshipDtoAssembler());
    }

    /**
     * @test
     */
    public function createsSiriusAssemblerWhenSourceIsSirius()
    {
        $assembler = (new LayDeputyshipDtoCollectionAssemblerFactory())->create([['source' => 'sirius']]);
        $this->assertInstanceOf(SiriusToLayDeputyshipDtoAssembler::class, $assembler->getLayDeputyshipDtoAssembler());
    }
}
