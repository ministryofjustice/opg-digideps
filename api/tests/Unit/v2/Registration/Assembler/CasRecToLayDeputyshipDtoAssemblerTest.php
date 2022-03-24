<?php

namespace App\Tests\Unit\v2\Registration\Assembler;

use App\v2\Registration\Assembler\CasRecToLayDeputyshipDtoAssembler;
use App\v2\Registration\DTO\LayDeputyshipDto;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CasRecToLayDeputyshipDtoAssemblerTest extends TestCase
{
    /** @var CasRecToLayDeputyshipDtoAssembler */
    private $sut;

    /** {@inheritDoc} */
    protected function setUp(): void
    {
        $this->sut = new CasRecToLayDeputyshipDtoAssembler();
    }

    /**
     * @test
     * @dataProvider getMissingDataVariations
     *
     * @param $itemToRemove
     */
    public function assembleFromArrayThrowsExceptionIfGivenIncompleteData($itemToRemove): void
    {
        $input = $this->getInput();
        unset($input[$itemToRemove]);

        $this->expectException(InvalidArgumentException::class);

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
            ['NDR'],
            ['Made Date'],
        ];
    }

    /** @test */
    public function assembleFromArrayAssemblesAndReturnsALayDeputyshipDto(): void
    {
        $result = $this->sut->assembleFromArray($this->getInput());

        $this->assertInstanceOf(LayDeputyshipDto::class, $result);
        $this->assertEquals('caset', $result->getCaseNumber());
        $this->assertEquals('surname', $result->getClientSurname());
        $this->assertEquals('deputy_no', $result->getDeputyUid());
        $this->assertEquals('deputysurname', $result->getDeputySurname());
        $this->assertEquals('deputypostcode', $result->getDeputyPostcode());
        $this->assertEquals('type_of_rep', $result->getTypeOfReport());
        $this->assertEquals('corref', $result->getCorref());
        $this->assertEquals(true, $result->isNdrEnabled());
        $this->assertEquals('2011-06-14', $result->getOrderDate()->format('Y-m-d'));
    }

    /**
     * @test
     * @dataProvider getNdrVariations
     *
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
            'Made Date' => '14-Jun-11',
        ];
    }
}
