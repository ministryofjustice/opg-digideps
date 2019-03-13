<?php

namespace Tests\AppBundle\v2\Dto;

use AppBundle\v2\DTO\ClientDto;
use AppBundle\v2\DTO\DeputyDto;
use JsonSerializable;
use PHPUnit\Framework\TestCase;

class DeputyDtoTest extends TestCase
{
    /** @var DeputyDto */
    private $dto;

    public function setUp()
    {
        $this->dto = new DeputyDto(
            435,
            'Alan',
            'Jones',
            'test@email.com',
            'ADMIN',
            'NG43 43DF',
            true
        );
    }

    /**
     * @test
     */
    public function isJsonSerializable()
    {
        $this->assertInstanceOf(JsonSerializable::class, $this->dto);
    }

    /**
     * @test
     * @dataProvider getClientVariations
     * @param array $clients
     * @param $expectedClientCount
     */
    public function jsonSerializeReturnsArrayRepresentationOfDto(array $clients, $expectedClientCount)
    {
        $this->dto->setClients($clients);
        $result = $this->dto->jsonSerialize();

        $this->assertCount(8, $result);
        $this->assertEquals('Alan', $result['firstname']);
        $this->assertEquals('Jones', $result['lastname']);
        $this->assertEquals('test@email.com', $result['email']);
        $this->assertEquals('ADMIN', $result['role_name']);
        $this->assertEquals('NG43 43DF', $result['address_postcode']);
        $this->assertEquals(true, $result['ndr_enabled']);
        $this->assertCount($expectedClientCount, $result['clients']);
    }

    /**
     * @return array
     */
    public function getClientVariations()
    {
        return [
            [
                'clients' => [
                    $this->getMockBuilder(ClientDto::class)->disableOriginalConstructor()->getMock(),
                    $this->getMockBuilder(ClientDto::class)->disableOriginalConstructor()->getMock()
                ],
                'expectedClientCount' => 2
            ],
            [
                'clients' => ['not-a-dto'],
                'expectedClientCount' => 0
            ],
            [
                'clients' => [],
                'expectedClientCount' => 0
            ]
        ];
    }
}
