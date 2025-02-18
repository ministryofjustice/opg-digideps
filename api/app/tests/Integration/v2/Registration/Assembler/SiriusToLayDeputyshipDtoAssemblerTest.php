<?php

namespace App\Tests\Unit\v2\Registration\Assembler;

use App\v2\Registration\Assembler\SiriusToLayDeputyshipDtoAssembler;
use App\v2\Registration\DTO\LayDeputyshipDto;
use PHPUnit\Framework\TestCase;

class SiriusToLayDeputyshipDtoAssemblerTest extends TestCase
{
    /** @var SiriusToLayDeputyshipDtoAssembler */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new SiriusToLayDeputyshipDtoAssembler();
    }

    /**
     * @test
     *
     * @dataProvider getMissingDataVariations
     */
    public function assembleFromArrayThrowsExceptionIfGivenIncompleteData($itemToRemove): void
    {
        $input = $this->getInput();
        unset($input[$itemToRemove]);

        $this->expectException(\InvalidArgumentException::class);

        $this->sut->assembleFromArray($input);
    }

    public function getMissingDataVariations(): array
    {
        return [
            ['Case'],
            ['ClientFirstname'],
            ['ClientSurname'],
            ['ClientAddress1'],
            ['ClientAddress2'],
            ['ClientAddress3'],
            ['ClientAddress4'],
            ['ClientAddress5'],
            ['ClientPostcode'],
            ['DeputyUid'],
            ['DeputyFirstname'],
            ['DeputySurname'],
            ['DeputyAddress1'],
            ['DeputyAddress2'],
            ['DeputyAddress3'],
            ['DeputyAddress4'],
            ['DeputyAddress5'],
            ['DeputyPostcode'],
            ['ReportType'],
            ['MadeDate'],
            ['OrderType'],
            ['CoDeputy'],
            ['Hybrid'],
        ];
    }

    /**
     * @test
     */
    public function assembleFromArrayThrowsExceptionIfGivenInvalidReportType(): void
    {
        $input = $this->getInput();
        $input['ReportType'] = 'invalidReportType';

        $this->expectException(\InvalidArgumentException::class);
        $this->sut->assembleFromArray($input);
    }

    /**
     * @test
     *
     * @dataProvider getReportTypeToCorrefExpectation
     */
    public function assembleFromArrayAssemblesAndReturnsALayDeputyshipDto($reportType): void
    {
        $input = $this->getInput();
        $input['ReportType'] = $reportType;

        $result = $this->sut->assembleFromArray($input);

        $this->assertInstanceOf(LayDeputyshipDto::class, $result);
        $this->assertEquals('caseT', $result->getCaseNumber());
        $this->assertEquals('firstname', $result->getClientFirstname());
        $this->assertEquals('surname', $result->getClientSurname());
        $this->assertEquals('client_postcode', $result->getClientPostcode());
        $this->assertEquals('client_address1', $result->getClientAddress1());
        $this->assertEquals('client_address2', $result->getClientAddress2());
        $this->assertEquals('client_address3', $result->getClientAddress3());
        $this->assertEquals('client_address4', $result->getClientAddress4());
        $this->assertEquals('client_address5', $result->getClientAddress5());
        $this->assertEquals('deputy_no', $result->getDeputyUid());
        $this->assertEquals('deputyfirstname', $result->getDeputyFirstname());
        $this->assertEquals('deputysurname', $result->getDeputySurname());
        $this->assertEquals('deputy_postcode', $result->getDeputyPostcode());
        $this->assertEquals('depaddress1', $result->getDeputyAddress1());
        $this->assertEquals('depaddress2', $result->getDeputyAddress2());
        $this->assertEquals('depaddress3', $result->getDeputyAddress3());
        $this->assertEquals('depaddress4', $result->getDeputyAddress4());
        $this->assertEquals('depaddress5', $result->getDeputyAddress5());
        $this->assertEquals($reportType, $result->getTypeOfReport());
        $this->assertEquals('pfa', $result->getOrderType());
        $this->assertEquals(false, $result->isNdrEnabled());
        $this->assertEquals('2011-06-14', $result->getOrderDate()->format('Y-m-d'));
        $this->assertEquals(true, $result->getIsCoDeputy());
    }

    public function getReportTypeToCorrefExpectation()
    {
        return [
            ['reportType' => 'OPG102'],
            ['reportType' => 'OPG103'],
        ];
    }

    private function getInput(): array
    {
        return [
            'Case' => 'caseT',
            'ClientFirstname' => 'firstname',
            'ClientSurname' => 'surname',
            'ClientAddress1' => 'client_address1',
            'ClientAddress2' => 'client_address2',
            'ClientAddress3' => 'client_address3',
            'ClientAddress4' => 'client_address4',
            'ClientAddress5' => 'client_address5',
            'ClientPostcode' => 'client_postcode',
            'DeputyUid' => 'deputy_no',
            'DeputyFirstname' => 'deputyfirstname',
            'DeputySurname' => 'deputysurname',
            'DeputyAddress1' => 'depaddress1',
            'DeputyAddress2' => 'depaddress2',
            'DeputyAddress3' => 'depaddress3',
            'DeputyAddress4' => 'depaddress4',
            'DeputyAddress5' => 'depaddress5',
            'DeputyPostcode' => 'deputy_postcode',
            'CoDeputy' => 'yes',
            'ReportType' => 'type_of_rep',
            'MadeDate' => '2011-06-14',
            'OrderType' => 'pfa',
            'Hybrid' => 'SINGLE',
        ];
    }
}
