<?php declare(strict_types=1);


namespace App\TestHelpers;

use App\Entity\Client;
use App\Entity\Report\Report;
use PHPUnit\Framework\TestCase;

class ClientTestHelper extends TestCase
{
    public function createClientMock(int $id, bool $hasReports)
    {
        $report = $hasReports ? (self::prophesize(Report::class))->reveal() : null;

        $client = self::prophesize(Client::class);
        $client->getReports()->willReturn($report);
        $client->getId()->willReturn($id);

        return $client->reveal();
    }
}
