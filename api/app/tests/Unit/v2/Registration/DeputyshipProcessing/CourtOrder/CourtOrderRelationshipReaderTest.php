<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\v2\Registration\DeputyshipProcessing\CourtOrder;

use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderKind;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CourtOrder\CourtOrderRelationshipReader;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\TestCase;

class CourtOrderRelationshipReaderTest extends TestCase
{
    public function testRead()
    {
        $connection = $this->createStub(Connection::class);
        $result = $this->createStub(Result::class);
        $reader = new CourtOrderRelationshipReader($connection);

        $connection->method('executeQuery')->willReturn($result);
        $result->method('iterateAssociative')->willReturn(new \ArrayIterator([
            ['client_id' => 11, 'order_id' => 1, 'sibling_id' => null, 'kind' => 'single'],
            ['client_id' => 13, 'order_id' => 3, 'sibling_id' => 7, 'kind' => 'dual'],
            ['client_id' => 15, 'order_id' => 5, 'sibling_id' => 9, 'kind' => 'hybrid'],
        ]));

        /**
         * @var array<array<int, CourtOrderKind, int|null>> $expectations
         */
        $expectations = [
            [11, 1, CourtOrderKind::Single, null],
            [13, 3, CourtOrderKind::Dual, 7],
            [15, 5, CourtOrderKind::Hybrid, 9],
        ];

        foreach ($reader->read() as $relationship) {
            $expectation = array_shift($expectations);
            $this->assertSame($expectation[0], $relationship->clientId);
            $this->assertSame($expectation[1], $relationship->courtOrderId);
            $this->assertSame($expectation[2], $relationship->kind);
            $this->assertSame($expectation[3], $relationship->siblingId);
        }
        $this->assertEmpty($expectations);
    }
}
