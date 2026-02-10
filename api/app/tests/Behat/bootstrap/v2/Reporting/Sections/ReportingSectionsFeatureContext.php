<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

use App\Entity\Client;
use App\Entity\Deputy;
use App\Entity\Report\Report;
use App\Tests\Behat\v2\Common\BaseFeatureContext;
use App\Tests\Behat\v2\Common\UserDetails;

class ReportingSectionsFeatureContext extends BaseFeatureContext
{
    use AccountsSectionTrait;
    use ActionsSectionTrait;
    use AdditionalInformationSectionTrait;
    use AssetsSectionTrait;
    use ClientBenefitsCheckSectionTrait;
    use ContactsSectionTrait;
    use DocumentsSectionTrait;
    use DeputyCostsSectionTrait;
    use DeputyCostsEstimateSectionTrait;
    use DebtsSectionTrait;
    use DecisionSectionTrait;
    use DeputyExpensesSectionTrait;
    use GiftsSectionTrait;
    use HealthAndLifestyleTrait;
    use MoneyInSectionTrait;
    use MoneyInShortSectionTrait;
    use MoneyOutSectionTrait;
    use MoneyOutShortSectionTrait;
    use MoneyTransferSectionTrait;
    use ReportOverviewTrait;
    use ReportingSectionsTrait;
    use VisitsCareSectionTrait;
    use IncomeBenefitsSectionTrait;

    public const string REPORT_SECTION_ENDPOINT = '/%s/%s/%s';

    public function createSubmittedReportForClient(int $clientId, string $type, \DateTime $submitDate): Report
    {
        $client = $this->em->getRepository(Client::class)->find($clientId);

        $report = $this->reportTestHelper->generateReport(
            $this->em,
            client: $client,
            type: $type,
            dateChecks: false,
        );

        $report->setSubmitted(true);
        $report->setSubmitDate($submitDate);

        $this->em->persist($report);
        $this->em->flush();

        return $report;
    }
}
