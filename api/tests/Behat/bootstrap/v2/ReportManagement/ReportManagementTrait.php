<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\ReportManagement;

use App\Tests\Behat\BehatException;
use DateTime;

trait ReportManagementTrait
{
    private array $baseReportCheckboxValuesAndTranslations = [
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

    private array $orgExtraCheckboxes = [
        'profDeputyCosts' => 'Deputy costs',
        'profDeputyCostsEstimate' => 'Deputy costs estimate',
    ];

    private array $layExtraCheckboxes = [
        'deputyExpenses' => 'Deputy expenses',
    ];

    /**
     * @When I manage the deputies :reportStatus report
     */
    public function iManageTheDeputiesSubmittedReport(string $reportStatus)
    {
        $this->iAmOnAdminClientDetailsPage();

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

        $isLay = is_null($this->interactingWithUserDetails->getOrganisationName);

        switch ($reportType) {
            case 'Health and welfare':
                $option = $isLay ? '104' : '104-5';
                break;
            case 'PFA high assets':
                $option = $isLay ? '102' : '102-5';
                break;
            case 'PFA low assets':
                $option = $isLay ? '103' : '103-5';
                break;
            case 'Combined high assets':
                $option = $isLay ? '102-4' : '102-4-5';
                break;
            case 'Combined low assets':
                $option = $isLay ? '103-4' : '103-4-5';
                break;
            default:
                throw new BehatException('Invalid report type - see options in ReportManagementTrait::iChangeReportTypeTo()');
        }

        $this->chooseOption('manage_report[type]', $option, 'manage-report');
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
        $this->selectOption('manage_report_confirm[confirm]', 'yes');
        $this->pressButton('Confirm');
    }

    /**
     * @Then the report details should be updated
     */
    public function reportDetailsShouldBeUpdated()
    {
        $this->iAmOnAdminClientDetailsPage();

        $reportPeriod = $this->interactingWithUserDetails->getCurrentReportPeriod();
        $locator = sprintf(
            "//td[normalize-space()='%s']/..",
            $reportPeriod
        );

        $reportRow = $this->getSession()->getPage()->find('xpath', $locator);

        if (is_null($reportRow)) {
            throw new BehatException(sprintf('Could not find a table data element with text %s on the page. HTML of page: %s', $reportPeriod, $this->getSession()->getPage()->find('xpath', '//main')->getHtml()));
        }

        $numberWeeksExtended = $this->getSectionAnswers('manage-report')[0]['manage_report[dueDateChoice]'];
        $expectedDueDate = (new DateTime())
            ->modify(
                sprintf('+ %s weeks', $numberWeeksExtended)
            )
            ->format('j F Y');

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
        $isLay = is_null($this->interactingWithUserDetails->getOrganisationName);

        $checkboxValuesAndTranslations = array_merge(
            $this->baseReportCheckboxValuesAndTranslations,
            $isLay ? $this->layExtraCheckboxes : $this->orgExtraCheckboxes
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
            $this->interactingWithUserDetails->setCurrentReportStartDate($dateObject);
        } else {
            $this->interactingWithUserDetails->setCurrentReportEndDate($dateObject);
        }
    }

    /**
     * @Then I should see the report sections the admin ticked as incomplete labelled as changes needed
     */
    public function iShouldSeeReportSectionsLabelledAsChangesNeeded()
    {
        $this->clickLink(
            sprintf(
                '%s to %s report',
                $this->interactingWithUserDetails->getCurrentReportStartDate()->format('Y'),
                $this->interactingWithUserDetails->getCurrentReportEndDate()->format('Y')
            )
        );

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
}
