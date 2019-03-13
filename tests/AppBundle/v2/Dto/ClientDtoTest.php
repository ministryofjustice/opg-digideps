<?php

namespace Tests\AppBundle\v2\Dto;

use AppBundle\v2\DTO\ClientDto;
use JsonSerializable;
use PHPUnit\Framework\TestCase;

class ClientDtoTest extends TestCase
{
    /** @var ClientDto */
    private $dto;

    public function setUp()
    {
        $this->dto = new ClientDto(
            435,
            '12345678',
            'Alan',
            'Jones',
            'test@email.com',
            6,
            8
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
     */
    public function jsonSerializeReturnsArrayRepresentationOfDto()
    {
        $result = $this->dto->jsonSerialize();

        $this->assertCount(7, $result);
        $this->assertEquals('12345678', $result['case_number']);
        $this->assertEquals('Alan', $result['firstname']);
        $this->assertEquals('Jones', $result['lastname']);
        $this->assertEquals('test@email.com', $result['email']);
        $this->assertEquals(6, $result['total_report_count']);
        $this->assertEquals(8, $result['ndrId']);
    }
}
