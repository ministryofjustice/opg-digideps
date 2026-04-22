<?php

declare(strict_types=1);

namespace Integration\Factory\MissingReport;

use App\Domain\CourtOrder\CourtOrderKind;
use App\Domain\CourtOrder\CourtOrderReportType;
use App\Domain\CourtOrder\CourtOrderType;
use App\Entity\Client;
use App\Entity\CourtOrder;
use App\Entity\Report\Report;
use App\Factory\MissingReport\MissingReportFinder;
use App\Tests\Integration\ApiIntegrationTestCase;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class MissingReportFinderTest extends ApiIntegrationTestCase
{
    public function setUp(): void
    {
        $metadata = self::$entityManager->getClassMetaData(CourtOrder::class);
        $this->oldGeneratorType = $metadata->generatorType;
        $this->oldGenerator = $metadata->idGenerator;

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());
    }

    public function tearDown(): void
    {
        $metadata = self::$entityManager->getClassMetaData(CourtOrder::class);
        $metadata->setIdGeneratorType($this->oldGeneratorType);
        $metadata->setIdGenerator($this->oldGenerator);
    }

    private function persistCourtOrder(int $id, Client $client, Report ...$reports): void
    {
        $courtOrder = new CourtOrder();
        $courtOrder->setId($id);
        $courtOrder->setClient($client);
        $courtOrder->setCourtOrderUid("UID-{$id}");
        $courtOrder->setOrderKind(CourtOrderKind::Single);
        $courtOrder->setOrderType(CourtOrderType::PFA);
        $courtOrder->setStatus('ACTIVE');
        $courtOrder->setOrderMadeDate(new \DateTime());
        $courtOrder->setOrderReportType(CourtOrderReportType::OPG102);
        $courtOrder->setSibling(null);
        foreach ($reports as $report) {
            $courtOrder->addReport($report);
        }
        self::$entityManager->persist($courtOrder);
    }

    private function persistReport(Client $client, ?bool $submitted): Report
    {
        $report = new Report($client, '102', new \DateTime(), new \DateTime(), false);
        $report->setSubmitted($submitted);
        self::$entityManager->persist($report);
        return $report;
    }

    private function persistTest(int $id, ?bool ...$submittedFlags): void
    {
        $client = new Client();
        self::$entityManager->persist($client);
        $reports = [];
        foreach ($submittedFlags as $submitted) {
            $reports[] = $this->persistReport($client, $submitted);
        }
        $this->persistCourtOrder($id, $client, ...$reports);
    }

    public function testFindCourtOrdersWithMissingReports()
    {
        $this->persistTest(1);
        $this->persistTest(2, null);
        $this->persistTest(3, false);
        $this->persistTest(4, true);
        $this->persistTest(5, null, null);
        $this->persistTest(6, true, null);
        $this->persistTest(7, false, null);
        $this->persistTest(8, null, false);
        $this->persistTest(9, false, false);
        $this->persistTest(10, true, false);
        $this->persistTest(11, null, true);
        $this->persistTest(12, false, true);
        $this->persistTest(13, true, true);
        $this->persistTest(14, null, true, false);
        self::$entityManager->flush();

        $finder = new MissingReportFinder(self::$entityManager);
        $orders = [...$finder->findCourtOrdersWithMissingReports()];
        $this->assertCount(3, $orders);
        $this->assertEqualsCanonicalizing([1, 4, 13], array_map(fn(CourtOrder $order) => $order->getId(), $orders));
    }
}
