<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

use App\Tests\Behat\v2\Common\BaseFeatureContext;

class ReportingSectionsFeatureContext extends BaseFeatureContext
{
    use AccountsSectionTrait;
    use ActionsSectionTrait;
    use AdditionalInformationSectionTrait;
    use ContactsSectionTrait;
    use DocumentsSectionTrait;
    use GiftsSectionTrait;
    use HealthAndLifestyleTrait;
    use MoneyOutShortSectionTrait;
    use MoneyOutSectionTrait;
    use VisitsCareSectionTrait;
    use MoneyInHighAssetsTrait;
    use DebtsSectionTrait;
    use AssetsSectionTrait;

    public const REPORT_SECTION_ENDPOINT = '/%s/%s/%s';

    /**
     * @Then the previous section should be :sectionName
     */
    public function previousSectionShouldBe(string $sectionName)
    {
        $anchor = $this->getSession()->getPage()->find('named', ['link', 'Navigate to previous part']);

        if (!$anchor) {
            $this->throwContextualException(
                'Previous section link is not visible on the page (searched by title = "Navigate to previous part")'
            );
        }

        $linkTextContainsSectionName = str_contains($anchor->getText(), $sectionName);

        if (!$linkTextContainsSectionName) {
            $this->throwContextualException(
                sprintf('Link contained unexpected text. Wanted: %s. Got: %s ', $sectionName, $anchor->getText())
            );
        }
    }

    /**
     * @Then the next section should be :sectionName
     */
    public function nextSectionShouldBe(string $sectionName)
    {
        $anchor = $this->getSession()->getPage()->find('named', ['link', 'Navigate to next part']);

        if (!$anchor) {
            $this->throwContextualException(
                'Next section link is not visible on the page (searched by title = "Navigate to next part")'
            );
        }

        $linkTextContainsSectionName = str_contains(strtolower($anchor->getText()), strtolower($sectionName));

        if (!$linkTextContainsSectionName) {
            $this->throwContextualException(
                sprintf('Link contained unexpected text. Wanted: %s. Got: %s ', $sectionName, $anchor->getText())
            );
        }
    }

    /**
     * @When I follow link back to report overview page
     */
    public function iNavigateBackToReportSection()
    {
        $this->iClickBasedOnAttributeTypeAndValue('a', 'data-action', 'report.overview');
        $this->iAmOnReportsOverviewPage();
    }

    /**
     * @When I press report sub section back button
     */
    public function iPressReportSubSectionBackButton()
    {
        $this->clickLink('Back');
    }

    /**
     * @When I view the report overview page
     */
    public function iGoToReportOverviewUrl()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportOverviewUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'overview');
        $this->visitPath($reportOverviewUrl);
    }

    /**
     * @When I view the NDR overview page
     */
    public function iGoToNDROverviewUrl()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportOverviewUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'overview');
        $this->visitPath($reportOverviewUrl);
    }

    /**
     * @When I should see :section as :status
     */
    public function iShouldSeeSectionAs($section, $status)
    {
        $this->iAmOnReportsOverviewPage();
        $sectionFormatted = sprintf('/%s/%s/%s', $this->reportUrlPrefix, $this->loggedInUserDetails->getCurrentReportId(), $section);
        $statusCorrect = false;
        $sectionExists = false;
        $divs = $this->findAllCssElements('div.opg-overview-section');

        foreach ($divs as $div) {
            if ($div->find('css', 'a')->getAttribute('href') === $sectionFormatted) {
                $sectionExists = true;
                $foundHtml = $div->getHtml();
                $statuses = $div->findAll('css', 'span');

                foreach ($statuses as $sts) {
                    if (str_contains(strtolower($sts->getHtml()), $status)) {
                        $statusCorrect = true;
                    }
                }
            }
        }

        if (!$sectionExists) {
            $this->throwContextualException(
                sprintf('href matching "%s" not found on %s.', $sectionFormatted, $this->getCurrentUrl())
            );
        }

        if (!$statusCorrect) {
            $this->throwContextualException(
                sprintf('Report section status not as expected. Status "%s" not found. Found: %s.', $status, $foundHtml)
            );
        }
    }

    /**
     * @Then I should see text asking to answer the question
     */
    public function iSeeTextRequestingToAnswerQuestion()
    {
        $table = $this->getSession()->getPage()->find('css', 'dl');

        if (!$table) {
            $this->throwContextualException('A dl element was not found on the page');
        }

        $tableEntry = $table->findAll('css', 'dd');

        if (!$tableEntry) {
            $this->throwContextualException('A dd element was not found on the page');
        }

        $furtherInfoNeeded = false;

        foreach ($tableEntry as $entry) {
            if (str_contains(trim(strtolower($entry->getHtml())), 'please answer this question')) {
                $furtherInfoNeeded = true;
            }
        }

        assert(
            $furtherInfoNeeded,
            'The text: "please answer this question" not found'
        );
    }

    /**
     * @When I choose to save and add another
     */
    public function iChooseToSaveAndAddAnother()
    {
        $this->pressButton('Save and add another');
    }

    /**
     * @When I choose to save and continue
     */
    public function iChooseToSaveAndContinue()
    {
        $this->pressButton('Save and continue');
    }

    public function moneyFormat($value)
    {
        return number_format(floatval($value), 2, '.', ',');
    }
}
