<?php

namespace Tests\AppBundle\v2\Assembler;

use AppBundle\v2\Assembler\ClientAssembler;
use AppBundle\v2\DTO\ClientDto;
use PHPUnit\Framework\TestCase;

class ClientAssemblerTest extends TestCase
{
    private $assembler;

    /** @var array  */
    private $input = [];

    /** @var ClientDto */
    private $result;

    public function setUp()
    {
        $this->assembler = new ClientAssembler();
    }

    /**
     * @test
     */
    public function assembleFromArrayReturnsDtoFromGivenArray()
    {
        $this
            ->buildValidInput()
            ->invokeAssembler()
            ->assertClientIsAssembledIntoDto();
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @dataProvider getMissingInputVariations
     * @param $fieldToEnsureIsMissing
     */
    public function assembleFromArrayThrowsExceptionIfGivenInsufficientData($fieldToEnsureIsMissing)
    {
        $this
            ->buildValidInput()
            ->ensureInputFieldIsMissing($fieldToEnsureIsMissing)
            ->invokeAssembler();
    }

    /**
     * @return array
     */
    public function getMissingInputVariations()
    {
        return [
            ['fieldToEnsureIsMissing' => 'id'],
            ['fieldToEnsureIsMissing' => 'case_number'],
            ['fieldToEnsureIsMissing' => 'firstname'],
            ['fieldToEnsureIsMissing' => 'lastname'],
            ['fieldToEnsureIsMissing' => 'email'],
            ['fieldToEnsureIsMissing' => 'report_count'],
            ['fieldToEnsureIsMissing' => 'ndr_id']
        ];
    }

    /**
     * @return ClientAssemblerTest
     */
    private function buildValidInput()
    {
        $this->input = [
            'id' => 123,
            'case_number' => '12345678',
            'firstname' => 'Bruce',
            'lastname' => 'Wayne',
            'email' => 'email@test.com',
            'report_count' => 21,
            'ndr_id' => 456
        ];

        return $this;
    }

    /**
     * @param $remove
     * @return ClientAssemblerTest
     */
    private function ensureInputFieldIsMissing($remove)
    {
        unset($this->input[$remove]);

        return $this;
    }

    /**
     * @return $this
     */
    private function invokeAssembler()
    {
        $this->result = $this->assembler->assembleFromArray($this->input);

        return $this;
    }

    /**
     * @return $this
     */
    private function assertClientIsAssembledIntoDto()
    {
        $this->assertInstanceOf(ClientDto::class, $this->result);
        $this->assertEquals(123, $this->result->getId());
        $this->assertEquals('12345678', $this->result->getCaseNumber());
        $this->assertEquals('Bruce', $this->result->getFirstName());
        $this->assertEquals('Wayne', $this->result->getLastName());
        $this->assertEquals('email@test.com', $this->result->getEmail());
        $this->assertEquals(21, $this->result->getReportCount());
        $this->assertEquals(456, $this->result->getNdrId());

        return $this;
    }
}
