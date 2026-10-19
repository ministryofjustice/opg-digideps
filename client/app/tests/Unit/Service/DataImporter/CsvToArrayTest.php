<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\DataImporter;

use OPG\Digideps\Frontend\Service\DataImporter\CsvToArray;
use PHPUnit\Framework\TestCase;

class CsvToArrayTest extends TestCase
{
    private array $columns = ['Case', 'Surname', 'Deputy No', 'Dep Surname'];
    private array $optionalColumns = ['Dep Postcode'];

    public function testGetData1With24Rows(): void
    {
        $sut = new CsvToArray(__DIR__ . '/csv1.csv', false);
        $sut->setExpectedColumns($this->columns);
        $sut->setOptionalColumns($this->optionalColumns);
        $data = $sut->getData();
        self::assertCount(24, $data);

        self::assertEquals([
            'Case' => '20000037',
            'Surname' => 'SMITH',
            'Deputy No' => '00063168',
            'Dep Surname' => 'SMITH',
            'Dep Postcode' => 'FY8 1FJ',
        ], $data[0]);

        self::assertEquals([
            'Case' => '20006813',
            'Surname'      => 'HOVIS',
            'Deputy No'    => '00000422',
            'Dep Surname'  => 'HOVIS',
            'Dep Postcode' => '',
        ], $data[8]);
    }

    public function testGetData2OptionalColumnsMissing(): void
    {
        $sut = new CsvToArray(__DIR__ . '/csv2.csv', false);
        $sut->setExpectedColumns($this->columns);
        $sut->setOptionalColumns($this->optionalColumns);
        $data = $sut->getData();

        self::assertEquals([
            [
                'Case'        => '20000037',
                'Surname'     => 'SMITH',
                'Deputy No'   => '00063168',
                'Dep Surname' => 'SMITH',
            ]
        ], $data);
    }

    public function testGetDataMissingFile(): void
    {
        self::expectException(\RuntimeException::class);

        new CsvToArray(__DIR__ . '/THISFILEDOESNOTEXIST.csv', false);
    }

    public function testGetDataInvalidFormat(): void
    {
        self::expectException(\RuntimeException::class);
        $sut = new CsvToArray(__DIR__ . '/invalid.csv', false);
        $sut->setExpectedColumns($this->columns);
        $sut->getData();
    }

    public function testGetDataEmpty(): void
    {
        $sut = new CsvToArray(__DIR__ . '/empty.csv', false);
        $sut->setExpectedColumns($this->columns);
        self::assertEquals([], $sut->getData());
    }

    public function testGetDataMissingColumns(): void
    {
        $sut = new CsvToArray(__DIR__ . '/missing-columns.csv', false);
        $sut->setExpectedColumns($this->columns);
        $sut->setExpectedColumns($this->columns);

        try {
            $sut->getData();
            self::fail(__METHOD__ . ': expected exception');
        } catch (\RuntimeException $e) {
            self::assertStringContainsString('Surname', $e->getMessage());
            self::assertStringContainsString('Dep Surname', $e->getMessage());
        }
    }

    public function testOneLineMissesRequiredColumn(): void
    {
        self::expectException(\RuntimeException::class);

        $sut = new CsvToArray(__DIR__ . '/broken-new-lines.csv', false);
        $sut->setExpectedColumns($this->columns);

        // this throws the exception
        $sut->getData();
    }
}
