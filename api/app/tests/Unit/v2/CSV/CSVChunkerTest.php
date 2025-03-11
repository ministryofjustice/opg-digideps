<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\CSV;

use App\v2\CSV\CSVChunker;
use PHPUnit\Framework\TestCase;

class CSVChunkerTest extends TestCase
{
    public function testGetChunkReturnsMultipleChunks(): void
    {
        $csvData = [
            ['row1'],
            ['row2'],
            ['row3'],
            ['row4'],
            ['row5'],
        ];

        $chunkSize = 2;

        $iterator = new \ArrayIterator($csvData);
        $chunker = new CSVChunker($iterator, $chunkSize);

        $this->assertEquals([['row1'], ['row2']], $chunker->getChunk());
        $this->assertEquals([['row3'], ['row4']], $chunker->getChunk());
        $this->assertEquals([['row5']], $chunker->getChunk());
        $this->assertEquals(null, $chunker->getChunk());
    }

    public function testGetChunkReturnsOneChunkWhenChunkSizeExceedsNumberOfItems(): void
    {
        $csvData = [
            ['row1'],
            ['row2'],
            ['row3'],
        ];

        $chunkSize = 5;

        $iterator = new \ArrayIterator($csvData);
        $chunker = new CSVChunker($iterator, $chunkSize);

        $this->assertEquals([['row1'], ['row2'], ['row3']], $chunker->getChunk());
        $this->assertEquals(null, $chunker->getChunk());
    }
}
