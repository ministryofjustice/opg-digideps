<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\Reporting\Sections;

use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Report\Report;
use Tests\OPG\Digideps\Backend\Behat\v2\Common\BaseFeatureContext;

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
