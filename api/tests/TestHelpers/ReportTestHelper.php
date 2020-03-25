<?php declare(strict_types=1);


namespace Tests\TestHelpers;


use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Report;
use DateInterval;
use DateTime;

class ReportTestHelper
{
    /**
     * @param Client|null $client
     * @param string|null $type
     * @param DateTime|null $startDate
     * @param DateTime|null $endDate
     * @return Report
     * @throws \Exception
     */
    public function generateReport(?Client $client=null, ?string $type=null, ?DateTime $startDate=null, ?DateTime $endDate=null)
    {
        $client = $client ? $client : (new ClientTestHelper())->generateClient();
        $type = $type ? $type : Report::TYPE_102;
        $startDate = $startDate ? $startDate : new \DateTime();
        $endDate = $endDate ? $endDate : (clone $startDate)->add(new DateInterval('P1Y'));

        return new Report($client, $type, $startDate, $endDate);
    }
}
