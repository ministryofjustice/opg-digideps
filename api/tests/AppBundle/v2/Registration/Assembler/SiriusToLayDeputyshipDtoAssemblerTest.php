<?php

namespace Tests\AppBundle\v2\Registration\Assembler;

use AppBundle\Entity\CasRec;
use AppBundle\Service\DataNormaliser;
use AppBundle\v2\Registration\Assembler\SiriusToLayDeputyshipDtoAssembler;
use AppBundle\v2\Registration\DTO\LayDeputyshipDto;
use PHPUnit\Framework\TestCase;

class SiriusToLayDeputyshipDtoAssemblerTest extends TestCase
{
    /** @var SiriusToLayDeputyshipDtoAssembler */
    private $sut;

    /** {@inheritDoc} */
    protected function setUp(): void
    {
        $this->sut = new SiriusToLayDeputyshipDtoAssembler(new DataNormaliser());
    }

    /**
     * @test
     * @dataProvider getMissingDataVariations
     * @param $itemToRemove
     */
    public function assembleFromArrayThrowsExceptionIfGivenIncompleteData($itemToRemove): void
    {
        $input = $this->getInput();
        unset($input[$itemToRemove]);

        $this->expectException(\InvalidArgumentException::class);

        $this->sut->assembleFromArray($input);
    }

    /** @return array */
    public function getMissingDataVariations(): array
    {
        return [
            ['Case'],
            ['Surname'],
            ['Deputy No'],
            ['Dep Surname'],
            ['Dep Postcode'],
            ['Typeofrep'],
            ['Corref']
        ];
    }

    /** @test */
    public function assembleFromArrayAssemblesAndReturnsALayDeputyshipDto(): void
    {
        $result = $this->sut->assembleFromArray($this->getInput());

        $this->assertInstanceOf(LayDeputyshipDto::class, $result);
        $this->assertEquals('caset', $result->getCaseNumber());
        $this->assertEquals('surname', $result->getClientSurname());
        $this->assertEquals('deputy_no', $result->getDeputyNumber());
        $this->assertEquals('deputysurname', $result->getDeputySurname());
        $this->assertEquals('deputypostcode', $result->getDeputyPostcode());
        $this->assertEquals('type_of_rep', $result->getTypeOfReport());
        $this->assertEquals('corref', $result->getCorref());
        $this->assertEquals(false, $result->isNdrEnabled());
        $this->assertEquals(CasRec::SIRIUS_SOURCE, $result->getSource());
    }

    /** @return array */
    private function getInput(): array
    {
        return [
            'Case' => 'caseT',
            'Surname' => 'surname',
            'Deputy No' => 'deputy_no',
            'Dep Surname' => 'deputysurname',
            'Dep Postcode' => 'deputy_postcode',
            'Typeofrep' => 'type_of_rep',
            'Corref' => 'corref',
            'Source' => 'will-use-constant-instead',
            'Not used' => 'not_used',
        ];
    }
}
