<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Factory;

use OPG\Digideps\Backend\Entity\Staging\StagingLayIngest;
use OPG\Digideps\Backend\Entity\Staging\StagingPaProIngest;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Common\CourtOrder\CourtOrderType;
use OPG\Digideps\Common\Deputy\DeputyType;

trait StagingFixtureTrait
{
    private function paProFixture(int $index, DeputyType $deputyType, ?\DateTimeImmutable $madeDate = null, CourtOrderReportType $courtOrderReportType = CourtOrderReportType::OPG102, CourtOrderType $courtOrderType = CourtOrderType::PFA, ?string $email = null): StagingPaProIngest
    {
        return new StagingPaProIngest(
            (string)(10000000 + $index),
            "ClientFirst{$index}",
            "ClientLast{$index}",
            new \DateTimeImmutable()->sub(new \DateInterval('P50Y')),
            "ClientAddress{$index} 1",
            "ClientAddress{$index} 2",
            "ClientAddress{$index} 3",
            "ClientAddress{$index} 4",
            "ClientAddress{$index} 5",
            "CL {$index}",
            $deputyType,
            (string)(50000000 + $index),
            $email ?? "Deputy{$index}@email{$index}.com",
            "Organisation {$index}",
            "DeputyFirst{$index}",
            "DeputyLast{$index}",
            "DeputyAddress{$index} 1",
            "DeputyAddress{$index} 2",
            "DeputyAddress{$index} 3",
            "DeputyAddress{$index} 4",
            "DeputyAddress{$index} 5",
            "DP {$index}",
            $madeDate ?? new \DateTimeImmutable()->sub(new \DateInterval('P1M')),
            $courtOrderReportType,
            $courtOrderType
        );
    }

    private function layFixture(int $index, ?\DateTimeImmutable $madeDate = null, CourtOrderReportType $courtOrderReportType = CourtOrderReportType::OPG102, CourtOrderType $courtOrderType = CourtOrderType::PFA): StagingLayIngest
    {
        return new StagingLayIngest(
            (string)(20000000 + $index),
            "ClientFirst{$index}",
            "ClientLast{$index}",
            "ClientAddress{$index} 1",
            "ClientAddress{$index} 2",
            "ClientAddress{$index} 3",
            "ClientAddress{$index} 4",
            "ClientAddress{$index} 5",
            "CL {$index}",
            (string)(60000000 + $index),
            "DeputyFirst{$index}",
            "DeputyLast{$index}",
            "DeputyAddress{$index} 1",
            "DeputyAddress{$index} 2",
            "DeputyAddress{$index} 3",
            "DeputyAddress{$index} 4",
            "DeputyAddress{$index} 5",
            "DP {$index}",
            $courtOrderReportType,
            $madeDate ?? new \DateTimeImmutable()->sub(new \DateInterval('P1M')),
            $courtOrderType
        );
    }
}
