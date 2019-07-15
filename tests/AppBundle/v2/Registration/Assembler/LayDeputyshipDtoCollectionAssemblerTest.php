<?php

namespace Tests\AppBundle\v2\Registration\Assembler;

use AppBundle\v2\Registration\Assembler\LayDeputyshipDtoAssemblerInterface;
use AppBundle\v2\Registration\Assembler\LayDeputyshipDtoCollectionAssembler;
use AppBundle\v2\Registration\DTO\LayDeputyshipDto;
use AppBundle\v2\Registration\DTO\LayDeputyshipDtoCollection;
use PHPUnit\Framework\TestCase;

class LayDeputyshipDtoCollectionAssemblerTest extends TestCase
{
    /** @var LayDeputyshipDtoCollectionAssembler */
    private $sut;

    /** @var LayDeputyshipDtoAssemblerInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $layDeputyshipDtoAssembler;

    /** @var LayDeputyshipDtoCollection */
    private $result;

    /** {@inheritDoc} */
    protected function setUp()
    {
        $this->layDeputyshipDtoAssembler = $this
            ->getMockBuilder(LayDeputyshipDtoAssemblerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sut = new LayDeputyshipDtoCollectionAssembler($this->layDeputyshipDtoAssembler);
    }

    /** @test */
    public function assembleFromArrayAssemblesACollectionAndReturnsIt(): void
    {
        $input = [
            ['alpha' => 'alpha-data'],
            ['beta' => 'beta-data']
        ];

        $this->layDeputyshipDtoAssembler->method('canAssemble')->willReturn(true);

        $this->assertEachItemWillBeAssembled($input);
        $this->result = $this->sut->assembleFromArray($input);
        $this->assertCollectionIsReturnedAndContainsEachAssembledItem();
    }

    /** @param array $input */
    private function assertEachItemWillBeAssembled(array $input): void
    {
        $this
            ->layDeputyshipDtoAssembler
            ->expects($this->exactly(count($input)))
            ->method('assembleFromArray')
            ->withConsecutive([['alpha' => 'alpha-data']], [['beta' => 'beta-data']])
            ->willReturn(new LayDeputyshipDto());
    }

    private function assertCollectionIsReturnedAndContainsEachAssembledItem(): void
    {
        $this->assertInstanceOf(LayDeputyshipDtoCollection::class, $this->result);
        $this->assertEquals(2, $this->result->count());
    }
}
