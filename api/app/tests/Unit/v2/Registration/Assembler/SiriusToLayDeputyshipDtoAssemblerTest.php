<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\v2\Registration\Assembler;

use OPG\Digideps\Backend\v2\Registration\Assembler\SiriusToLayDeputyshipDtoAssembler;
use OPG\Digideps\Backend\v2\Registration\DTO\LayDeputyshipDto;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SiriusToLayDeputyshipDtoAssemblerTest extends TestCase
{
    private SiriusToLayDeputyshipDtoAssembler $sut;

    protected function setUp(): void
    {
        $this->sut = new SiriusToLayDeputyshipDtoAssembler();
    }

    #[DataProvider('getMissingDataVariations')]
    #[Test]
    public function assembleFromArrayThrowsExceptionIfGivenIncompleteData(string $itemToRemove, string $errorType): void
    {
        $input = $this->getInput();
        unset($input[$itemToRemove]);

        /** @var class-string<\Throwable> $errorType */
        $this->expectException($errorType);

        $this->sut->assembleFromArray($input);
    }

    /**
     * @return array{string, class-string<\Throwable>}[]
     */
    public static function getMissingDataVariations(): array
    {
        return [
            ['Case', \TypeError::class],
            ['ClientSurname', \InvalidArgumentException::class],
            ['DeputyUid', \TypeError::class],
            ['DeputyFirstname', \TypeError::class],
            ['DeputySurname', \TypeError::class],
            ['DeputyPostcode', \TypeError::class],
            ['ReportType', \InvalidArgumentException::class],
            ['MadeDate', \InvalidArgumentException::class],
            ['OrderType', \InvalidArgumentException::class],
            ['CoDeputy', \InvalidArgumentException::class],
            ['Hybrid', \InvalidArgumentException::class],
        ];
    }

    #[Test]
    public function assembleFromArrayThrowsExceptionIfGivenInvalidReportType(): void
    {
        $input = $this->getInput();
        $input['ReportType'] = 'invalidReportType';

        $this->expectException(\InvalidArgumentException::class);
        $this->sut->assembleFromArray($input);
    }

    #[DataProvider('getReportTypeToCorrefExpectation')]
    #[Test]
    public function assembleFromArrayAssemblesAndReturnsALayDeputyshipDto(string $reportType): void
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
        $this->assertEquals('11223344', $result->getDeputyUid());
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
        $this->assertEquals('2011-06-14', $result->getOrderDate()->format('Y-m-d'));
        $this->assertEquals(true, $result->getIsCoDeputy());
    }

    public static function getReportTypeToCorrefExpectation(): array
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
            'DeputyUid' => '11223344',
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
