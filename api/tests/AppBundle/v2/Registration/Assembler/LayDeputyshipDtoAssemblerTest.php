<?php

namespace Tests\AppBundle\v2\Registration\Assembler;

use AppBundle\Service\DataNormaliser;
use AppBundle\v2\Registration\Assembler\CasRecToLayDeputyshipDtoAssembler;
use AppBundle\v2\Registration\DTO\LayDeputyshipDto;
use PHPUnit\Framework\TestCase;

class LayDeputyshipDtoAssemblerTest extends TestCase
{
    /** @var CasRecToLayDeputyshipDtoAssembler */
    private $sut;

    /** {@inheritDoc} */
    protected function setUp(): void
    {
        $this->sut = new CasRecToLayDeputyshipDtoAssembler(new DataNormaliser());
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
            ['Corref'],
            ['NDR']
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
        $this->assertEquals(true, $result->isNdrEnabled());
    }

    /**
     * @test
     * @dataProvider getNdrVariations
     * @param $ndrValue
     * @param $expected
     */
    public function assembleFromArrayDeterminesIfNdrEnabled($ndrValue, $expected): void
    {
        $input = $this->getInput();
        $input['NDR'] = $ndrValue;

        $result = $this->sut->assembleFromArray($input);
        $this->assertEquals($expected, $result->isNdrEnabled());
    }

    /** @return array */
    public function getNdrVariations(): array
    {
        return [
            ['ndrValue' => 'Y', 'expected' => true],
            ['ndrValue' => 1, 'expected' => true],
            ['ndrValue' => 'N', 'expected' => false],
            ['ndrValue' => 0, 'expected' => false],
            ['ndrValue' => null, 'expected' => false],
            ['ndrValue' => '', 'expected' => false],
        ];
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
            'NDR' => 'Y',
            'Not used' => 'not_used',
        ];
    }
}
