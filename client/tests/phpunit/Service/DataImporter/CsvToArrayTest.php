<?php

namespace AppBundle\Service\DataImporter;

use JMS\Serializer\Exception\RuntimeException;

class CsvToArrayTest extends \PHPUnit_Framework_TestCase
{
    private $columns = ['Case', 'Surname', 'Deputy No', 'Dep Surname'];
    private $optionalColumns = ['Dep Postcode'];

    public function testgetData1With24Rows()
    {
        $object = new CsvToArray(__DIR__ . '/csv1.csv', false);
        $object->setExpectedColumns($this->columns);
        $object->setOptionalColumns($this->optionalColumns);
        $data = $object->getData();
        $this->assertCount(24, $data);

        $this->assertEquals(['Case'         => '20000037',
                             'Surname'      => 'SMITH',
                             'Deputy No'    => '00063168',
                             'Dep Surname'  => 'SMITH',
                             'Dep Postcode' => 'FY8 1FJ',], $data[0]);

        $this->assertEquals(['Case'         => '20006813',
                             'Surname'      => 'HOVIS',
                             'Deputy No'    => '00000422',
                             'Dep Surname'  => 'HOVIS',
                             'Dep Postcode' => '',], $data[8]);
    }

    public function testgetData2OptionalColumnsMissing()
    {
        $object = new CsvToArray(__DIR__ . '/csv2.csv', false);
        $object->setExpectedColumns($this->columns);
        $object->setOptionalColumns($this->optionalColumns);
        $data = $object->getData();


        $this->assertEquals([['Case'        => '20000037',
                              'Surname'     => 'SMITH',
                              'Deputy No'   => '00063168',
                              'Dep Surname' => 'SMITH',
        ]], $data);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testgetDataMissingFile()
    {
        new CsvToArray(__DIR__ . '/THISFILEDOESNOTEXIST.csv', false);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testgetDataInvalidFormat()
    {
        $object = new CsvToArray(__DIR__ . '/invalid.csv', false);
        $object->setExpectedColumns($this->columns);
        $object->getData();
    }

    public function testgetDataEmpty()
    {
        $object = new CsvToArray(__DIR__ . '/empty.csv', false);
        $object->setExpectedColumns($this->columns);
        $this->assertEquals([], $object->getData());
    }

    public function testgetDataMissingColumns()
    {
        $object = new CsvToArray(__DIR__ . '/missing-columns.csv', false);
        $object->setExpectedColumns($this->columns);
        $object->setExpectedColumns($this->columns);

        try {
            $object->getData();
            $this->fail(__METHOD__ . ': expected exception');
        } catch (\RuntimeException $e) {
            $this->assertContains('Surname', $e->getMessage());
            $this->assertContains('Dep Surname', $e->getMessage());
        }
    }

    /**
     * @expectedException RuntimeException
     */
    public function testOneLineMissesRequiredColumn()
    {
        $object = new CsvToArray(__DIR__ . '/broken-new-lines.csv', false);
        $object->setExpectedColumns($this->columns);
        $data = $object->getData();
    }
}
