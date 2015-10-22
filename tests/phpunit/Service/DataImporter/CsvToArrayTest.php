<?php

namespace AppBundle\Service\DataImporter;

class CsvToArrayTest extends \PHPUnit_Framework_TestCase
{
    private $columns = ['Case','Surname', 'Deputy No', 'Dep Surname', 'Dep Postcode'];
    
    public function testgetData1()
    {
        $object = new CsvToArray(__DIR__ . '/csv1.csv');
        $object->setExpectedColumns($this->columns);
        $data = $object->getData();

        $this->assertCount(24, $data);

        $this->assertEquals(['Case' => '20000037',
            'Surname' => 'SMITH',
            'Deputy No' => '00063168',
            'Dep Surname' => 'SMITH',
            'Dep Postcode' => 'FY8 1FJ'], $data[0]);


        $this->assertEquals(['Case' => '20006813',
            'Surname' => 'HOVIS',
            'Deputy No' => '00000422',
            'Dep Surname' => 'HOVIS',
            'Dep Postcode' => ''], $data[8]);
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testgetDataMissingFile()
    {
        new CsvToArray(__DIR__ . '/THISFILEDOESNOTEXIST.csv');
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testgetDataInvalidFormat()
    {
        $object = new CsvToArray(__DIR__ . '/invalid.csv');
        $object->setExpectedColumns($this->columns);
        $object->getData();
    }
    
    public function testgetDataMissingColumns()
    {
        $object = new CsvToArray(__DIR__ . '/missing-columns.csv');
        $object->setExpectedColumns($this->columns);
        
        try {
            $object->getData();
            $this->fail(__METHOD__.': expected exception');
        } catch (\RuntimeException $e) {
            $this->assertContains('Missing Header columns: Surname, Dep Surname', $e->getMessage());
        }
    }

}