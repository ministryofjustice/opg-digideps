<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\Reporting\Sections;

use OPG\Digideps\Common\CourtOrder\CourtOrderType;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\User;
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
        $user = $this->em->getRepository(User::class)->find($this->loggedInUserDetails?->getUserId() ?? 0);
        $deputy = $user->getDeputy() ?? $this->fixtureHelper->createDeputy($user->getEmail(), user: $user);
        $this->em->persist($deputy);

        $report = $this->reportTestHelper->generateReport(
            $this->em,
            client: $client,
            type: $type,
            dateChecks: false,
        );

        $this->fixtureHelper->createAndPersistCourtOrder(CourtOrderType::PFA, $client, $deputy, $report);

        $report->setSubmitted(true);
        $report->setSubmitDate($submitDate);

        $this->em->persist($report);
        $this->em->flush();

        return $report;
    }
}
