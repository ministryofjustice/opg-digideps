<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

use App\Tests\Behat\v2\Common\BaseFeatureContext;

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

    public const REPORT_SECTION_ENDPOINT = '/%s/%s/%s';
}
