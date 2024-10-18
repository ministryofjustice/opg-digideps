<?php

namespace App\Tests\Unit\v2\Registration\Assembler;

use App\v2\Registration\Assembler\LayPreRegistrationDtoAssemblerInterface;
use App\v2\Registration\Assembler\LayPreRegistrationDtoCollectionAssembler;
use App\v2\Registration\DTO\LayPreRegistrationDto;
use App\v2\Registration\DTO\LayPreRegistrationDtoCollection;
use PHPUnit\Framework\TestCase;

class LayPreRegistrationDtoCollectionAssemblerTest extends TestCase
{
    /** @var LayPreRegistrationDtoCollectionAssembler */
    private $sut;

    /** @var LayPreRegistrationDtoAssemblerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $layPreRegistrationDtoAssembler;

    /** @var LayPreRegistrationDtoCollection */
    private $result;

    protected function setUp(): void
    {
        $this->layPreRegistrationDtoAssembler = $this
            ->getMockBuilder(LayPreRegistrationDtoAssemblerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sut = new LayPreRegistrationDtoCollectionAssembler($this->layPreRegistrationDtoAssembler);
    }

    /** @test */
    public function assembleFromArrayAssemblesACollectionAndReturnsIt(): void
    {
        $input = [
            ['alpha' => 'alpha-data'],
            ['beta' => 'beta-data'],
        ];

        $this->assertEachItemWillBeAssembled($input);
        $this->result = $this->sut->assembleFromArray($input);
        $this->assertCollectionIsReturnedAndContainsEachAssembledItem();
    }

    private function assertEachItemWillBeAssembled(array $input): void
    {
        $this
            ->layPreRegistrationDtoAssembler
            ->expects($this->exactly(count($input)))
            ->method('assembleFromArray')
            ->withConsecutive([['alpha' => 'alpha-data']], [['beta' => 'beta-data']])
            ->willReturn(new LayPreRegistrationDto());
    }

    private function assertCollectionIsReturnedAndContainsEachAssembledItem(): void
    {
        $this->assertInstanceOf(LayPreRegistrationDtoCollection::class, $this->result['collection']);
        $this->assertEquals(2, $this->result['collection']->count());
    }

    /** @test */
    public function assembleFromDoesNotAddInvalidNodesToItsCollection(): void
    {
        $input = [
            ['alpha' => 'not-valid-enough-to-create-a-DTO'],
            ['beta' => 'beta-data'],
        ];

        $this
            ->layPreRegistrationDtoAssembler
            ->expects($this->exactly(count($input)))
            ->method('assembleFromArray')
            ->withConsecutive([['alpha' => 'not-valid-enough-to-create-a-DTO']], [['beta' => 'beta-data']])
            ->willReturnOnConsecutiveCalls(null, new LayPreRegistrationDto());

        $this->result = $this->sut->assembleFromArray($input);
        $this->assertEquals(1, $this->result['collection']->count());
    }
}
