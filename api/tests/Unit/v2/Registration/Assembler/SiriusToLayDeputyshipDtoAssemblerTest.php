<?php

namespace App\Tests\Unit\v2\Registration\Assembler;

use App\Entity\CasRec;
use App\v2\Registration\Assembler\SiriusToLayDeputyshipDtoAssembler;
use App\v2\Registration\DTO\LayDeputyshipDto;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SiriusToLayDeputyshipDtoAssemblerTest extends TestCase
{
    /** @var SiriusToLayDeputyshipDtoAssembler */
    private $sut;

    /** {@inheritDoc} */
    protected function setUp(): void
    {
        $this->sut = new SiriusToLayDeputyshipDtoAssembler();
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
            ['Made Date'],
        ];
    }

    /**
     * @test
     */
    public function assembleFromArrayReturnsNullIfGivenUnexpectedReportType(): void
    {
        $input = $this->getInput();
        $input['Typeofrep'] = 'OPG104';

        $this->assertNull($this->sut->assembleFromArray($input));
    }

    /**
     * @test
     * @dataProvider getReportTypeToCorrefExpectation
     */
    public function assembleFromArrayAssemblesAndReturnsALayDeputyshipDto($reportType, $expectedCorref): void
    {
        $input = $this->getInput();
        $input['Typeofrep'] = $reportType;

        $result = $this->sut->assembleFromArray($input);

        $this->assertInstanceOf(LayDeputyshipDto::class, $result);
        $this->assertEquals('caset', $result->getCaseNumber());
        $this->assertEquals('surname', $result->getClientSurname());
        $this->assertEquals('deputy_no', $result->getDeputyUid());
        $this->assertEquals('deputysurname', $result->getDeputySurname());
        $this->assertEquals('deputypostcode', $result->getDeputyPostcode());
        $this->assertEquals($reportType, $result->getTypeOfReport());
        $this->assertEquals($expectedCorref, $result->getCorref());
        $this->assertEquals(false, $result->isNdrEnabled());
        $this->assertEquals(CasRec::SIRIUS_SOURCE, $result->getSource());
        $this->assertEquals('2011-06-14', $result->getOrderDate()->format('Y-m-d'));
    }

    public function getReportTypeToCorrefExpectation()
    {
        return [
            ['reportType' => 'OPG102', 'expectedCorref' => 'L2'],
            ['reportType' => 'OPG103', 'expectedCorref' => 'L3'],
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
            'Source' => 'will-use-constant-instead',
            'Not used' => 'not_used',
            'Made Date' => '14-Jun-11',
        ];
    }
}
