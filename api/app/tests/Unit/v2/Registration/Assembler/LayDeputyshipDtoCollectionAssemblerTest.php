<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\v2\Registration\Assembler;

use PHPUnit\Framework\Attributes\Test;
use Exception;
use OPG\Digideps\Backend\v2\Registration\Assembler\LayDeputyshipDtoAssemblerInterface;
use OPG\Digideps\Backend\v2\Registration\Assembler\LayDeputyshipDtoCollectionAssembler;
use OPG\Digideps\Backend\v2\Registration\DTO\LayDeputyshipDto;
use OPG\Digideps\Backend\v2\Registration\DTO\LayDeputyshipDtoCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class LayDeputyshipDtoCollectionAssemblerTest extends TestCase
{
    private LayDeputyshipDtoCollectionAssembler $sut;
    private LayDeputyshipDtoAssemblerInterface&MockObject $layDeputyshipDtoAssembler;
    private LayDeputyshipDtoCollection|array $result;

    protected function setUp(): void
    {
        $this->layDeputyshipDtoAssembler = $this
            ->getMockBuilder(LayDeputyshipDtoAssemblerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sut = new LayDeputyshipDtoCollectionAssembler($this->layDeputyshipDtoAssembler);
    }

    #[Test]
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
            ->willReturnCallback(
                fn ($param): LayDeputyshipDto =>
                match ($param) {
                    ['alpha' => 'alpha-data'], ['beta' => 'beta-data'] => new LayDeputyshipDto(),
                    default => throw new Exception('Did not expect input ' . print_r($param, true)),
                }
            );
    }

    private function assertCollectionIsReturnedAndContainsEachAssembledItem(): void
    {
        $this->assertInstanceOf(LayDeputyshipDtoCollection::class, $this->result['collection']);
        $this->assertEquals(2, $this->result['collection']->count());
    }

    #[Test]
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
            ->willReturnCallback(
                fn ($param): ?LayDeputyshipDto =>
                    match ($param) {
                        ['alpha' => 'not-valid-enough-to-create-a-DTO'] => null,
                        ['beta' => 'beta-data'] => new LayDeputyshipDto(),
                        default => throw new Exception('Did not expect input ' . print_r($param, true)),
                    }
            );

        $this->result = $this->sut->assembleFromArray($input);
        $this->assertEquals(1, $this->result['collection']->count());
    }
}
