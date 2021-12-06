<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\ReportManagement;

use App\Entity\Report\Report;
use App\Entity\User;
use App\Tests\Behat\BehatException;
use DateTime;

trait ReportManagementTrait
{
    private array $baseCombinedHighReportCheckboxValuesAndTranslations = [
        'decisions' => 'Decisions',
        'contacts' => 'Contacts',
        'visitsCare' => 'Visits and care',
        'lifestyle' => 'Health and lifestyle',
        'bankAccounts' => 'Accounts',
        'moneyTransfers' => 'Money transfers',
        'moneyIn' => 'Money in',
        'moneyOut' => 'Money out',
        'assets' => 'Assets',
        'debts' => 'Debts',
        'gifts' => 'Gifts',
        'balance' => 'Accounts balance check',
        'actions' => 'Actions you plan to take',
        'otherInfo' => 'Any other information',
        'documents' => 'Supporting documents',
    ];

    private array $profCombinedHighExtraCheckboxes = [
        'profDeputyCosts' => 'Deputy costs',
        'profDeputyCostsEstimate' => 'Deputy costs estimate',
    ];

    private array $paCombinedHighExtraCheckboxes = [
        'paDeputyExpenses' => 'Deputy fees and expenses',
    ];

    private array $layCombinedHighExtraCheckboxes = [
        'deputyExpenses' => 'Deputy expenses',
    ];

    protected string $reportStatus = '';

    /**
     * @When I manage the deputies :reportStatus report
     */
    public function iManageTheDeputiesSubmittedReport(string $reportStatus)
    {
        $this->iAmOnAdminClientDetailsPage();
        $this->reportStatus = $reportStatus;

        $reportId = 'completed' === $reportStatus ? $this->interactingWithUserDetails->getCurrentReportId() : $this->interactingWithUserDetails->getPreviousReportId();

        $xpathLocator = sprintf(
            "//a[contains(@href,'/admin/report/%s/manage')]",
            $reportId
        );

        $reportLink = $this->getSession()->getPage()->find('xpath', $xpathLocator);
        $reportLink->click();
    }

    /**
     * @When I change the report type to :reportType
     */
    public function iChangeReportTypeTo(string $reportType)
    {
        $this->iAmOnAdminManageReportPage();
        $this->assertInteractingWithUserIsSet();

        $roleType = $this->translateDeputyRole($this->interactingWithUserDetails->getUserRole());

        $reportTypes = [
            'Health and welfare' => [User::TYPE_PA => Report::PA_HW_TYPE, User::TYPE_PROF => Report::PROF_HW_TYPE, User::TYPE_LAY => Report::LAY_HW_TYPE],
            'PFA high assets' => [User::TYPE_PA => Report::PA_PFA_HIGH_ASSETS_TYPE, User::TYPE_PROF => Report::PROF_PFA_HIGH_ASSETS_TYPE, User::TYPE_LAY => Report::LAY_PFA_HIGH_ASSETS_TYPE],
            'PFA low assets' => [User::TYPE_PA => Report::PA_PFA_LOW_ASSETS_TYPE, User::TYPE_PROF => Report::PROF_PFA_LOW_ASSETS_TYPE, User::TYPE_LAY => Report::LAY_PFA_LOW_ASSETS_TYPE],
            'Combined high assets' => [User::TYPE_PA => Report::PA_COMBINED_HIGH_ASSETS_TYPE, User::TYPE_PROF => Report::PROF_COMBINED_HIGH_ASSETS, User::TYPE_LAY => Report::LAY_COMBINED_HIGH_ASSETS_TYPE],
            'Combined low assets' => [User::TYPE_PA => Report::PA_COMBINED_LOW_ASSETS_TYPE, User::TYPE_PROF => Report::PROF_COMBINED_LOW_ASSETS, User::TYPE_LAY => Report::LAY_COMBINED_LOW_ASSETS_TYPE],
        ];

        switch ($reportType) {
            case 'Health and welfare':
                $option = $reportTypes['Health and welfare'][$roleType];
                break;
            case 'PFA high assets':
                $option = $reportTypes['PFA high assets'][$roleType];
                break;
            case 'PFA low assets':
                $option = $reportTypes['PFA low assets'][$roleType];
                break;
            case 'Combined high assets':
                $option = $reportTypes['Combined high assets'][$roleType];
                break;
            case 'Combined low assets':
                $option = $reportTypes['Combined low assets'][$roleType];
                break;
            default:
                throw new BehatException('Invalid report type - see options in ReportManagementTrait::iChangeReportTypeTo()');
        }

        $this->chooseOption('manage_report[type]', $option, 'manage-report');
    }

    private function translateDeputyRole(string $role): string
    {
        if (str_contains($role, User::TYPE_PROF)) {
            return User::TYPE_PROF;
        }

        if (str_contains($role, User::TYPE_PA)) {
            return User::TYPE_PA;
        }

        if (str_contains($role, User::TYPE_LAY)) {
            return User::TYPE_LAY;
        }

        throw new BehatException('Unrecognised role - valid deputy roles must contain LAY, PROF or PA');
    }

    /**
     * @When I change the report due date to :numberOfWeeks weeks from now
     */
    public function iChangeReportDueDateToWeeks(string $numberOfWeeks)
    {
        $this->iAmOnAdminManageReportPage();

        if (!in_array($numberOfWeeks, ['3', '4', '5'])) {
            throw new BehatException('Due date weeks available are 3, 4 or 5 - rewrite this step to use a valid option');
        }

        $this->fillInField('manage_report[dueDateChoice]', $numberOfWeeks, 'manage-report');
    }

    /**
     * @When I submit the new report details
     */
    public function iSubmitNewReportDetails()
    {
        $this->iAmOnAdminManageReportPage();
        $this->pressButton('Continue');

        $this->iAmOnAdminManageReportConfirmPage();

        $confirmRadio = $this->getSession()->getPage()->find('xpath', '//input[@name="manage_report_confirm[confirm]"]');

        if (!is_null($confirmRadio)) {
            $this->selectOption('manage_report_confirm[confirm]', 'yes');
        }

        $this->pressButton('Confirm');
    }

    /**
     * @Then the report details should be updated
     */
    public function reportDetailsShouldBeUpdated()
    {
        $this->iAmOnAdminClientDetailsPage();

        if ('completed' === $this->reportStatus) {
            $reportPeriod = $this->interactingWithUserDetails->getCurrentReportPeriod();
            $reportDueDate = $this->interactingWithUserDetails->getCurrentReportDueDate();
        } else {
            $reportPeriod = $this->interactingWithUserDetails->getPreviousReportPeriod();
            $reportDueDate = $this->interactingWithUserDetails->getPreviousReportDueDate();
        }

        $locator = sprintf(
            "//td[normalize-space()='%s']/..",
            $reportPeriod
        );

        $reportRow = $this->getSession()->getPage()->find('xpath', $locator);

        if (is_null($reportRow)) {
            throw new BehatException(sprintf('Could not find a table data element with text %s on the page. HTML of page: %s', $reportPeriod, $this->getSession()->getPage()->find('xpath', '//main')->getHtml()));
        }

        $numberWeeksExtended = $this->getSectionAnswers('manage-report')[0] ?? null;

        if ($numberWeeksExtended['manage_report[dueDateChoice]'] ?? null) {
            $expectedDueDate = (new DateTime())
                ->modify(
                    sprintf('+ %s weeks', $numberWeeksExtended)
                )
                ->format('j F Y');
        } else {
            $expectedDueDate = $reportDueDate->format('j F Y');
        }

        $this->assertStringContainsString(
            $expectedDueDate,
            $reportRow->getHtml(),
            'Comparing form answers against report row of client details page'
        );
    }

    /**
     * @When I confirm all report sections are incomplete
     */
    public function confirmAllReportSectionsIncomplete()
    {
        $this->iAmOnAdminManageReportPage();
        $roleType = $this->translateDeputyRole($this->interactingWithUserDetails->getUserRole());

        if (User::TYPE_LAY === $roleType) {
            $extraFields = $this->layCombinedHighExtraCheckboxes;
        } elseif (User::TYPE_PA === $roleType) {
            $extraFields = $this->paCombinedHighExtraCheckboxes;
        } else {
            $extraFields = $this->profCombinedHighExtraCheckboxes;
        }

        if ($this->clientBenefitsSectionAvailable) {
            $extraFields['clientBenefitsCheck'] = 'Benefits check and income other people receive';
        }

        $checkboxValuesAndTranslations = array_merge(
            $this->baseCombinedHighReportCheckboxValuesAndTranslations,
            $extraFields
        );

        foreach ($checkboxValuesAndTranslations as $value => $translation) {
            $this->tickCheckbox(
                'incompleteSectionsForm',
                $this->determineCheckboxName($value, $checkboxValuesAndTranslations),
                'manage-report',
                $translation)
            ;
        }
    }

    private function determineCheckboxName(string $value, array $checkboxValuesAndTranslations)
    {
        $checkboxDictionary = array_flip(array_keys($checkboxValuesAndTranslations));

        return sprintf('manage_report[unsubmittedSection][%s][present]', $checkboxDictionary[$value]);
    }

    /**
     * @When I change the report :event date to :date
     */
    public function iChangeReportEventToDate(string $event, string $date)
    {
        $this->iAmOnAdminManageReportPage();

        if (!in_array($event, ['start', 'end'])) {
            throw new BehatException('$event must be either "start" or "end"');
        }

        $dateObject = new DateTime($date);

        $this->fillInDateFields(
            sprintf('manage_report[%sDate]', $event),
            intval($dateObject->format('j')),
            intval($dateObject->format('n')),
            intval($dateObject->format('Y')),
            'manage-report'
        );

        if ('start' === $event) {
            if ('completed' === $this->reportStatus) {
                $this->interactingWithUserDetails->setCurrentReportStartDate($dateObject);
            } else {
                $this->interactingWithUserDetails->setPreviousReportStartDate($dateObject);
            }
        } else {
            if ('completed' === $this->reportStatus) {
                $this->interactingWithUserDetails->setCurrentReportEndDate($dateObject);
            } else {
                $this->interactingWithUserDetails->setPreviousReportEndDate($dateObject);
            }
        }
    }

    /**
     * @Then I should see the report sections the admin ticked as incomplete labelled as changes needed
     */
    public function iShouldSeeReportSectionsLabelledAsChangesNeeded()
    {
        if ('completed' === $this->reportStatus) {
            $reportPeriod = sprintf(
                '%s to %s report',
                $this->interactingWithUserDetails->getCurrentReportStartDate()->format('Y'),
                $this->interactingWithUserDetails->getCurrentReportEndDate()->format('Y')
            );
        } else {
            $reportPeriod = sprintf(
                '%s to %s report',
                $this->interactingWithUserDetails->getPreviousReportStartDate()->format('Y'),
                $this->interactingWithUserDetails->getPreviousReportEndDate()->format('Y')
            );
        }

        $this->clickLink($reportPeriod);

        $sectionsMarkedIncomplete = $this->getSectionAnswers('manage-report')[0]['incompleteSectionsForm'];

        foreach ($sectionsMarkedIncomplete as $incompleteSectionName) {
            $locator = sprintf("//li//a[normalize-space()='%s']/../../..", $incompleteSectionName);
            $sectionListItem = $this->getSession()->getPage()->find('xpath', $locator);

            if (is_null($sectionListItem)) {
                throw new BehatException(sprintf('Could not find a list item containing an anchor element with text "%s". HTML of page: %s', $incompleteSectionName, $this->getSession()->getPage()->find('xpath', '//main')->getHtml()));
            }

            $this->assertStringContainsString(
                'Changes needed',
                $sectionListItem->getHtml(),
                'Searching for "Changes needed" in list item that contains incomplete section name'
            );
        }
    }

    /**
     * @When I close the un-submitted report
     */
    public function iCloseUnsubmittedReport()
    {
        $this->iAmOnAdminClientDetailsPage();

        $reportPeriod = 'completed' === $this->reportStatus ? $this->interactingWithUserDetails->getCurrentReportPeriod() : $this->interactingWithUserDetails->getPreviousReportPeriod();
        $locator = sprintf(
            "//td[normalize-space()='%s']/..",
            $reportPeriod
        );

        $reportRow = $this->getSession()->getPage()->find('xpath', $locator);

        if (is_null($reportRow)) {
            throw new BehatException(sprintf('Could not find a table data element with text %s on the page. HTML of page: %s', $reportPeriod, $this->getSession()->getPage()->find('xpath', '//main')->getHtml()));
        }

        $reportRow->clickLink('Manage');

        $this->iAmOnAdminManageReportPage();

        $this->checkOption('manage_report_close[agreeCloseReport]');
        $this->pressButton('Close report');

        $this->iAmOnAdminManageCloseReportConfirmPage();
        $this->pressButton('Confirm close report');
    }

    /**
     * @Then the report should should show as submitted
     */
    public function theReportShouldShouldShowAsSubmitted()
    {
        $this->iAmOnAdminClientDetailsPage();

        $reportPeriod = 'completed' === $this->reportStatus ? $this->interactingWithUserDetails->getCurrentReportPeriod() : $this->interactingWithUserDetails->getPreviousReportPeriod();
        $locator = sprintf(
            "//td[normalize-space()='%s']/../../..",
            $reportPeriod
        );

        $reportRow = $this->getSession()->getPage()->find('xpath', $locator);

        if (is_null($reportRow)) {
            throw new BehatException(sprintf('Could not find a table data element with text %s on the page. HTML of page: %s', $reportPeriod, $this->getSession()->getPage()->find('xpath', '//main')->getHtml()));
        }

        $submittedStatus = $reportRow->find('xpath', '//span[normalize-space()="submitted"]');

        if (is_null($submittedStatus)) {
            throw new BehatException(sprintf('Could not find a span element with the text "submitted" in the report row for "%s". HTML of page: %s', $reportPeriod, $this->getSession()->getPage()->find('xpath', '//main')->getHtml()));
        }
    }

    /**
     * @Then the link to download the submitted report should be visible
     */
    public function theLinkToDownloadTheSubmittedReportShouldBeVisible()
    {
        $this->iAmOnAdminClientDetailsPage();

        $xpathLocator = sprintf(
            "//a[contains(@href,'/report/deputyreport-%s.pdf')]",
            $this->interactingWithUserDetails->getPreviousReportId()
        );

        $reportPdfLink = $this->getSession()->getPage()->find('xpath', $xpathLocator);

        if (is_null($reportPdfLink)) {
            throw new BehatException('Could not find download link for the report');
        }
    }

    /**
     * @Then the link to download the submitted report should not be visible
     */
    public function theLinkToDownloadTheSubmittedReportShouldNotBeVisible()
    {
        $this->iAmOnAdminClientDetailsPage();

        $xpathLocator = sprintf(
            "//a[contains(@href,'/report/deputyreport-%s.pdf')]",
            $this->interactingWithUserDetails->getPreviousReportId()
        );

        $reportPdfLink = $this->getSession()->getPage()->find('xpath', $xpathLocator);

        if (!is_null($reportPdfLink)) {
            throw new BehatException('Download link for the report is visible when it should not be');
        }
    }

    /**
     * @Given /^I should not see the client benefits check section in the checklist group$/
     */
    public function iShouldNotSeeTheClientBenefitsCheckSectionInTheChecklistGroup()
    {
        $this->assertClientBenefitsCheckboxVisible(false);
    }

    /**
     * @Given /^I should see the client benefits check section in the checklist group$/
     */
    public function iShouldSeeTheClientBenefitsCheckSectionInTheChecklistGroup()
    {
        $this->assertClientBenefitsCheckboxVisible(true);
    }

    private function assertClientBenefitsCheckboxVisible(bool $shouldBeVisible)
    {
        $benefitsCheckXpath = './/label[text()[contains(.,"Client benefits check")]]/..';

        $checkboxDiv = $this->getSession()->getPage()->find('xpath', $benefitsCheckXpath);

        $checkboxIsVisible = !is_null($checkboxDiv);

        if ($shouldBeVisible) {
            if (!$checkboxIsVisible) {
                $message = sprintf(
                    'The checkbox for "Client benefits check" appeared on the page when it shouldn\'t have: %s',
                    $checkboxDiv->getHtml()
                );

                throw new BehatException($message);
            }
        } else {
            if ($checkboxIsVisible) {
                $message = sprintf(
                    'The checkbox for "Client benefits check" did not appear on the page when it should have: %s',
                    $this->getSession()->getPage()->find('xpath', '//main')->getHtml()
                );

                throw new BehatException($message);
            }
        }
    }
}
