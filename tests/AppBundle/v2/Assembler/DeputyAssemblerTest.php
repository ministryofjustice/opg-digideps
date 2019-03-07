<?php

namespace Tests\AppBundle\v2\Assembler;

use AppBundle\v2\Assembler\ClientAssembler;
use AppBundle\v2\Assembler\DeputyAssembler;
use AppBundle\v2\DTO\ClientDto;
use AppBundle\v2\DTO\DeputyDto;
use PHPUnit\Framework\TestCase;

class DeputyAssemblerTest extends TestCase
{
    private $assembler;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $clientAssembler;

    /** @var array  */
    private $input = [];

    /** @var DeputyDto */
    private $result;

    public function setUp()
    {
        $this->clientAssembler = $this->getMock(ClientAssembler::class);
        $this->assembler = new DeputyAssembler($this->clientAssembler);
    }

    /**
     * @test
     */
    public function assembleFromArrayReturnsDtoFromGivenArray()
    {
        $this
            ->buildValidInput()
            ->ensureClientAssemblerWillAssemble()
            ->invokeAssembler()
            ->assertDeputyIsAssembledIntoDto()
            ->assertClientsAreAssembledIntoDtos();
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
            ['fieldToEnsureIsMissing' => 'firstname'],
            ['fieldToEnsureIsMissing' => 'lastname'],
            ['fieldToEnsureIsMissing' => 'email'],
            ['fieldToEnsureIsMissing' => 'role_name'],
            ['fieldToEnsureIsMissing' => 'address_postcode'],
            ['fieldToEnsureIsMissing' => 'odr_enabled'],
            ['fieldToEnsureIsMissing' => 'clients']
        ];
    }

    /**
     * @return DeputyAssemblerTest
     */
    private function buildValidInput()
    {
        $this->input = [
            'id' => 123,
            'firstname' => 'Peter',
            'lastname' => 'Jones',
            'email' => 'email@test.com',
            'role_name' => 'ADMIN',
            'address_postcode' => 'NG2 2SA',
            'odr_enabled' => true,
            'clients' => [['foo' => 'client'], ['bar' => 'client']]
        ];

        return $this;
    }

    /**
     * @param $remove
     * @return DeputyAssemblerTest
     */
    private function ensureInputFieldIsMissing($remove)
    {
        unset($this->input[$remove]);

        return $this;
    }

    /**
     * @return $this
     */
    private function ensureClientAssemblerWillAssemble()
    {
        $this->clientAssembler
            ->expects($this->exactly(count($this->input['clients'])))
            ->method('assembleFromArray')
            ->willReturnOnConsecutiveCalls(
                $this->getMockBuilder(ClientDto::class)->disableOriginalConstructor()->getMock(),
                $this->getMockBuilder(ClientDto::class)->disableOriginalConstructor()->getMock()
            );

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
    private function assertDeputyIsAssembledIntoDto()
    {
        $this->assertInstanceOf(DeputyDto::class, $this->result);
        $this->assertEquals(123, $this->result->getId());
        $this->assertEquals('Peter', $this->result->getFirstName());
        $this->assertEquals('Jones', $this->result->getLastName());
        $this->assertEquals('email@test.com', $this->result->getEmail());
        $this->assertEquals('ADMIN', $this->result->getRoleName());
        $this->assertEquals('NG2 2SA', $this->result->getPostcode());
        $this->assertEquals(true, $this->result->getNdrEnabled());

        return $this;
    }

    /**
     * @return $this
     */
    private function assertClientsAreAssembledIntoDtos()
    {
        $this->assertInternalType('array', $this->result->getClients());
        $this->assertCount(2, $this->result->getClients());
        $this->assertInstanceOf(ClientDto::class, $this->result->getClients()[0]);
        $this->assertInstanceOf(ClientDto::class, $this->result->getClients()[1]);
        $this->assertNotSame($this->result->getClients()[0], $this->result->getClients()[1]);

        return $this;
    }
}
