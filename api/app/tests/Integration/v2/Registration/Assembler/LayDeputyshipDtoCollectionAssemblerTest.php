<?php

namespace App\Tests\Integration\v2\Registration\Assembler;

use App\v2\Registration\Assembler\LayDeputyshipDtoAssemblerInterface;
use App\v2\Registration\Assembler\LayDeputyshipDtoCollectionAssembler;
use App\v2\Registration\DTO\LayDeputyshipDto;
use App\v2\Registration\DTO\LayDeputyshipDtoCollection;
use PHPUnit\Framework\TestCase;

class LayDeputyshipDtoCollectionAssemblerTest extends TestCase
{
    /** @var LayDeputyshipDtoCollectionAssembler */
    private $sut;

    /** @var LayDeputyshipDtoAssemblerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $layDeputyshipDtoAssembler;

    /** @var LayDeputyshipDtoCollection */
    private $result;

    protected function setUp(): void
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
            ['beta' => 'beta-data'],
        ];

        $this->assertEachItemWillBeAssembled($input);
        $this->result = $this->sut->assembleFromArray($input);
        $this->assertCollectionIsReturnedAndContainsEachAssembledItem();
    }

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
        $this->assertInstanceOf(LayDeputyshipDtoCollection::class, $this->result['collection']);
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
            ->layDeputyshipDtoAssembler
            ->expects($this->exactly(count($input)))
            ->method('assembleFromArray')
            ->withConsecutive([['alpha' => 'not-valid-enough-to-create-a-DTO']], [['beta' => 'beta-data']])
            ->willReturnOnConsecutiveCalls(null, new LayDeputyshipDto());

        $this->result = $this->sut->assembleFromArray($input);
        $this->assertEquals(1, $this->result['collection']->count());
    }
}
