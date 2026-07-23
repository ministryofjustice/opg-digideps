<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Factory\RequiredReport;

use Doctrine\ORM\Id\AbstractIdGenerator;
use OPG\Digideps\Common\CourtOrder\CourtOrderKind;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Common\CourtOrder\CourtOrderType;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Factory\RequiredReport\RequiredReportFinder;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;

class RequiredReportFinderTest extends ApiIntegrationTestCase
{
    /**
     * @var int $oldGeneratorType
     * @phpstan-var ClassMetadata::GENERATOR_TYPE_* $oldGeneratorType
     */
    private int $oldGeneratorType;
    private AbstractIdGenerator $oldGenerator;


    public function setUp(): void
    {
        parent::setUp();
        $metadata = self::$entityManager->getClassMetaData(CourtOrder::class);
        $this->oldGeneratorType = $metadata->generatorType;
        $this->oldGenerator = $metadata->idGenerator;

        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $metadata = self::$entityManager->getClassMetaData(CourtOrder::class);
        $metadata->setIdGeneratorType($this->oldGeneratorType);
        $metadata->setIdGenerator($this->oldGenerator);
    }

    private function persistCourtOrder(int $id, Client $client, Report ...$reports): void
    {
        $courtOrder = new CourtOrder(
            "UID-{$id}",
            CourtOrderType::PFA,
            CourtOrderReportType::OPG102,
            CourtOrderKind::Single,
            new \DateTime(),
            $client
        )->setId($id);

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

    public function testFindCourtOrdersWithoutRequiredReports(): void
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

        $finder = new RequiredReportFinder(self::$entityManager);
        $orders = [...$finder->findCourtOrdersWithoutRequiredReports()];
        $this->assertCount(3, $orders);
        $this->assertEqualsCanonicalizing([1, 4, 13], array_map(fn (CourtOrder $order) => $order->getId(), $orders));
    }
}
