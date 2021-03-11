<?php declare(strict_types=1);


namespace App\TestHelpers;

use App\Entity\Client;
use App\Entity\Report\Report;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManager;

class ReportTestHelper
{
    /**
     * @param EntityManager $em
     * @param Client|null $client
     * @param string|null $type
     * @param DateTime|null $startDate
     * @param DateTime|null $endDate
     * @return Report
     */
    public function generateReport(EntityManager $em, ?Client $client=null, ?string $type=null, ?DateTime $startDate=null, ?DateTime $endDate=null)
    {
        $client = $client ? $client : (new ClientTestHelper())->generateClient($em);
        $type = $type ? $type : Report::TYPE_102;
        $startDate = $startDate ? $startDate : new \DateTime();
        $endDate = $endDate ? $endDate : (clone $startDate)->add(new DateInterval('P1Y'));

        return new Report($client, $type, $startDate, $endDate);
    }
}
