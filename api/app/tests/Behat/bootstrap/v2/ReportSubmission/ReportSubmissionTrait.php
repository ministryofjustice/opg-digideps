<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\ReportSubmission;

use Behat\Mink\Element\NodeElement;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use OPG\Digideps\Backend\Entity\Report\Document;
use OPG\Digideps\Backend\Entity\Report\ReportSubmission;
use Tests\OPG\Digideps\Backend\Behat\BehatException;
use Tests\OPG\Digideps\Backend\Behat\v2\Common\UserDetails;

trait ReportSubmissionTrait
{
    private array $documentFileNames = [];

    private string $reportSubmissionStandardAndNdr_CaseNumber;

    /**
     * @Then I should see the case number of the user I'm interacting with
     */
    public function iShouldSeeInteractingWithCaseNumber(): void
    {
        $this->assertInteractingWithUserIsSet();

        $caseNumber = $this->getCaseNumberFromUserDetails($this->interactingWithUserDetails);
        $locator = sprintf('//td[normalize-space()="%s"]/..', $caseNumber);
        $submissionRow = $this->getSession()->getPage()->find('xpath', $locator);

        if (is_null($submissionRow)) {
            throw new BehatException(sprintf('Could not find a submission row that contained case number "%s"', $caseNumber));
        }
    }

    #[When('I attach a supporting document :imageName to the report')]
    public function iAttachedASupportingDocumentToTheCompletedReport(string $imageName): void
    {
        $this->iAmOnUploadDocumentPage();
        $this->attachDocument($imageName);
    }

    private function attachDocument(string $imageName): void
    {
        $this->attachFileToField('report_document_upload_files', $imageName);
        $this->pressButton('Upload');
    }

    #[When('I view the pending submissions')]
    public function iViewPendingSubmissions(): void
    {
        $this->clickLink('Pending');
    }

    #[Then('the document :filename should be queued')]
    public function documentShouldBeQueued(string $fileName): void
    {
        $this->assertRowWithStatusAppears($fileName, 'Queued');
    }

    #[Then('the document :filename should be synced')]
    public function documentShouldBeSynced(string $fileName): void
    {
        $this->clickLink('Synchronised');

        $this->assertRowWithStatusAppears($fileName, 'Success');
    }

    #[Given('/^I run the document\-sync command$/')]
    public function iRunTheDocumentSyncCommand(): void
    {
        $this->visitAdminPath('/admin/behat/run-document-sync-command');

        if ($this->getSession()->getStatusCode() > 299) {
            throw new BehatException('There was an non successful response when running the document-sync command');
        }

        sleep(1);
    }

    #[Given('/^the report PDF document should be synced$/')]
    public function theReportPDFDocumentShouldBeSynced(): void
    {
        $this->assertRowWithStatusAppears('DigiRep-', 'Success');
    }

    #[When('I attach a "second" supporting document :imageName to the submitted report')]
    #[When('I attach a supporting document :imageName to the submitted report')]
    public function attachSupportingDocumentToSubmittedReport(string $imageName): void
    {
        $this->iVisitTheDocumentsStep2Page();
        $this->assertPageNotContainsText('Send documents');
        $this->attachDocument($imageName);
    }

    #[Given('/^I send the documents to complete the upload process on the submitted report$/')]
    public function iSendTheDocumentsToCompleteTheUploadProcess(): void
    {
        $this->clickLink('Send documents');
    }

    #[When('I search for submissions using the :whichNameSearched name of the clients with the same :whichNamesAreSame name')]
    public function iSearchForSubmissionsUsingTheFirstNameOfTheClientsWithTheSameFirstName(
        string $whichNameSearched,
        string $whichNamesAreSame,
    ): void {
        /** @var ?UserDetails $userDetails */
        $userDetails = $whichNamesAreSame === 'first' ? $this->sameFirstNameUserDetails[0] : $this->sameLastNameUserDetails[0];
        $nameToSearchOn = $whichNameSearched === 'first' ? $userDetails?->getClientFirstName() : $userDetails?->getClientLastName();

        if ($nameToSearchOn === null) {
            throw new BehatException('Could not find a name to search on; user details likely to be null');
        }

        $this->fillInField('q', $nameToSearchOn);
        $this->pressButton('Search');
        $this->clickLink('Pending');
    }

    #[Then('I should see the clients with the same :whichName names in the search results')]
    public function iShouldSeeBothClientsInTheSearchResults(string $whichName): void
    {
        /** @var array<UserDetails> $usersToSearchOn */
        $usersToSearchOn = $whichName === 'first' ? $this->sameFirstNameUserDetails : $this->sameLastNameUserDetails;
        $locator = sprintf(
            '//td[normalize-space()="%s"]|//td[normalize-space()="%s"]',
            $this->getCaseNumberFromUserDetails($usersToSearchOn[0] ?? null),
            $this->getCaseNumberFromUserDetails($usersToSearchOn[1] ?? null),
        );

        $clientRows = $this->getSession()->getPage()->findAll('xpath', $locator);

        $this->assertIntEqualsInt(
            2,
            count($clientRows),
            sprintf('Count rows that contain case numbers of clients that have the same %s name', $whichName)
        );
    }

    #[Then('I should not see the two clients with different :whichName names')]
    public function iShouldNotSeeTheOtherTwoClientsWithDifferentNames(string $whichName): void
    {
        /** @var array<UserDetails> $usersToSearchOn */
        $usersToSearchOn = $whichName === 'first' ? $this->sameFirstNameUserDetails : $this->sameLastNameUserDetails;
        $locator = sprintf(
            '//td[normalize-space()="%s"]|//td[normalize-space()="%s"]',
            $this->getCaseNumberFromUserDetails($usersToSearchOn[0] ?? null),
            $this->getCaseNumberFromUserDetails($usersToSearchOn[1] ?? null),
        );

        $clientRows = $this->getSession()->getPage()->findAll('xpath', $locator);

        $this->assertIntEqualsInt(
            0,
            count($clientRows),
            sprintf('Count rows that contain case numbers of clients that have the same %s name', $whichName)
        );
    }

    #[When('I search for submissions using the court order number of the client with :numberReports report(s)')]
    public function iSearchForSubmissionsUsingTheCourtOrderNumberOfTheClientWithNumberReports(string $numberReports): void
    {
        $userToSearchOn = $numberReports === 'one' ? $this->oneReportsUserDetails : $this->twoReportsUserDetails;
        $this->fillInField('q', $this->getCaseNumberFromUserDetails($userToSearchOn));
        $this->pressButton('Search');
        $this->clickLink('Pending');
    }

    #[Then('I should see :numberRows rows for the client with :numberReports report submission(s) in the search results')]
    public function iShouldSeeNumberRowsForClientWithNumberReports(string $numberRows, string $numberReports): void
    {
        $userToSearchOn = $numberReports === 'one' ? $this->oneReportsUserDetails : $this->twoReportsUserDetails;
        $locator = sprintf(
            '//td[normalize-space()="%s"]',
            $this->getCaseNumberFromUserDetails($userToSearchOn)
        );

        $clientRows = $this->getSession()->getPage()->findAll('xpath', $locator);

        $expectedRows = $numberRows === 'one' ? 1 : 2;
        $this->assertIntEqualsInt(
            $expectedRows,
            count($clientRows),
            sprintf('Count rows that contain case numbers of clients that has submitted %s reports', $numberReports)
        );
    }

    #[Then('I should not see the client with :numberReports report submission(s) in the search results')]
    public function iShouldNotSeeTheClientWithSubmissionsInResults(string $numberReports): void
    {
        $userToSearchOn = $numberReports === 'one' ? $this->oneReportsUserDetails : $this->twoReportsUserDetails;

        $locator = sprintf(
            '//td[normalize-space()="%s"]',
            $this->getCaseNumberFromUserDetails($userToSearchOn)
        );

        $clientRows = $this->getSession()->getPage()->findAll('xpath', $locator);

        $this->assertIntEqualsInt(
            0,
            count($clientRows),
            sprintf('Count rows that contain case numbers of clients that has submitted %s reports', $numberReports)
        );
    }

    #[When('I manually :action the client that has one submitted report')]
    public function iManuallyArchiveTheClientThatHasOneSubmittedReport(string $action): void
    {
        $locator = sprintf(
            '//td[normalize-space()="%s"]/..//input',
            $this->getCaseNumberFromUserDetails($this->oneReportsUserDetails)
        );

        $clientRowCheckBox = $this->getSession()->getPage()->find('xpath', $locator);
        if (!($clientRowCheckBox instanceof NodeElement)) {
            throw new BehatException('Could not find a checkbox for the client');
        }
        $clientRowCheckBox->check();
        $this->pressButton($action === 'archive' ? 'Archive' : 'Synchronise');
    }

    #[Then('I should see the client row under the Synchronised tab')]
    public function iShouldSeeTheClientRowUnderTheSynchronisedTab(): void
    {
        $this->clickLink('Synchronised');
        $this->iShouldSeeNumberRowsForClientWithNumberReports('one', 'one');
    }

    #[Given('there was an error during synchronisation')]
    public function thereWasAnErrorDuringSync(): void
    {
        $submittedReportId = $this->oneReportsUserDetails?->getPreviousReportId();
        if ($submittedReportId === null) {
            throw new BehatException('User does not have a previous report ID');
        }

        $submission = $this->em->getRepository(ReportSubmission::class)->findOneBy(['report' => $submittedReportId]);
        if (!($submission instanceof ReportSubmission)) {
            throw new BehatException('Could not find a report submission with ID ' . $submittedReportId);
        }

        foreach ($submission->getDocuments() as $document) {
            $document->setSynchronisationStatus(Document::SYNC_STATUS_PERMANENT_ERROR);
            $this->documentFileNames[] = $document->getFilename();
            $this->em->persist($document);
        }

        $this->em->flush();
    }

    #[Then('the status of the documents for the client with one report submission should be :status')]
    public function statusOfSubmissionDocumentsShouldBe(string $status): void
    {
        $locator = sprintf(
            '//td[normalize-space()="%s"]/../..',
            $this->getCaseNumberFromUserDetails($this->oneReportsUserDetails)
        );

        $submissionRowTableBody = $this->getSession()->getPage()->find('xpath', $locator);
        if (!($submissionRowTableBody instanceof NodeElement)) {
            throw new BehatException('Could not find table element containing submission rows');
        }

        /** @var string $documentFileName */
        foreach ($this->documentFileNames as $documentFileName) {
            $locator = sprintf(
                '//td[normalize-space()="%s"]/..',
                $documentFileName
            );

            $documentRow = $submissionRowTableBody->find('xpath', $locator);

            if (!($documentRow instanceof NodeElement)) {
                $errorMessage = sprintf(
                    'Could not find a row that contained the status "%s" for submission with court order number "%s". Table HTML: %s',
                    $status,
                    $this->getCaseNumberFromUserDetails($this->oneReportsUserDetails),
                    $submissionRowTableBody->getHtml()
                );

                throw new BehatException($errorMessage);
            }

            $this->assertStringContainsString(
                $status,
                $documentRow->getHtml(),
                'Comparing expected status against status in table row that contains an expected filename'
            );
        }
    }

    #[Then('/^the \'([^\']*)\' tab \'([^\']*)\' visible$/')]
    public function tabVisibilityCheck(string $tabName, string $visibility): void
    {
        $shouldBeVisible = $visibility === 'is';
        $newSubmissionTab = $this->getSession()->getPage()->find('css', "a:contains('$tabName')");

        if ($shouldBeVisible && !$newSubmissionTab) {
            $errorMessage = "The 'New' tab is not visible when it should be";
            throw new BehatException($errorMessage);
        }

        if (!$shouldBeVisible && $newSubmissionTab) {
            $errorMessage = "The 'New' tab is visible when it shouldn't be";
            throw new BehatException($errorMessage);
        }
    }

    #[When('I search for submissions using the case number of the deputy I am interacting with and check the :status column')]
    public function iSearchForSubmissionsUsingTheCaseNumberOfTheDeputyIAmInteractingWithForTheStatusColumn(string $status): void
    {
        $caseNumber = $this->getCaseNumberFromUserDetails($this->interactingWithUserDetails);
        $this->fillInField('q', $caseNumber);
        $this->pressButton('Search');
        $this->clickLink($status);
    }

    #[Then('I should not see the submission under the :status tab with the court order number of the user I am interacting with')]
    #[Then('I should see the submission under the :status tab with the court order number of the user I am interacting with')]
    public function submissionBehaviourBasedOnStatus(string $status): void
    {
        $caseNumber = $this->getCaseNumberFromUserDetails($this->interactingWithUserDetails);
        $reportPdfRow = $this->getSession()->getPage()->find('css', "table tr:contains('$caseNumber')");

        if ($status === 'New') {
            if (!is_null($reportPdfRow)) {
                throw new BehatException("The submission ($caseNumber) appears in the new column when it should not appear");
            }
        } elseif ($status === 'Pending') {
            if (is_null($reportPdfRow)) {
                throw new BehatException("The submission ($caseNumber) does not appear in the pending column when it should appear");
            }
        }
    }

    private function assertRowWithStatusAppears(string $searchTerm, string $status): void
    {
        $reportPdfRow = $this->getSession()->getPage()->find(
            'css',
            sprintf('table tr:contains("%s")', $searchTerm)
        );

        if (is_null($reportPdfRow)) {
            throw new BehatException(sprintf('Cannot find a table row that contains %s. Page content: %s', $searchTerm, $this->getSession()->getPage()->getHtml()));
        }

        if (!str_contains($reportPdfRow->getHtml(), $status)) {
            throw new BehatException(sprintf('The document does not have a status of %s. Row content: %s', $status, $reportPdfRow->getHtml()));
        }
    }

    #[Then('there should be :numReports report on the org dashboard page')]
    #[Then('there should be :numReports reports on the org dashboard page')]
    public function thereShouldBeNReports(int $numReports): void
    {
        $rows = $this->findAllCssElements('.behat-region-client');

        $actualNumReports = count($rows);
        $this->assertIntEqualsInt($numReports, $actualNumReports, "expected $numReports reports, got $actualNumReports");
    }

    private function getCaseNumberFromUserDetails(?UserDetails $userDetails): string
    {
        $caseNumber = $userDetails?->getClientCaseNumber();

        if ($caseNumber === null) {
            throw new BehatException('Unable to find a case number for test; user details likely to be null');
        }

        return $caseNumber;
    }
}
