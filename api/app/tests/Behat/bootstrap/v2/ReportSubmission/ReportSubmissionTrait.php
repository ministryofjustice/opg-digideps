<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\ReportSubmission;

use App\Entity\Report\Document;
use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Service\ParameterStoreService;
use App\Tests\Behat\BehatException;

trait ReportSubmissionTrait
{
    private array $documentFileNames = [];

    /**
     * @Then I should see the case number of the user I'm interacting with
     */
    public function iShouldSeeInteractingWithCaseNumber()
    {
        $this->assertInteractingWithUserIsSet();

        $caseNumber = $this->interactingWithUserDetails->getClientCaseNumber();
        $locator = sprintf('//td[normalize-space()="%s"]/..', $caseNumber);
        $submissionRow = $this->getSession()->getPage()->find('xpath', $locator);

        if (is_null($submissionRow)) {
            throw new BehatException(sprintf('Could not find a submission row that contained case number "%s"', $caseNumber));
        }
    }

    /**
     * @When I attach a supporting document :imageName to the report
     */
    public function iAttachedASupportingDocumentToTheCompletedReport(string $imageName)
    {
        $this->iAmOnUploadDocumentPage();
        $this->attachDocument($imageName);
    }

    private function attachDocument(string $imageName)
    {
        $this->attachFileToField('report_document_upload_files', $imageName);
        $this->pressButton('Upload');
    }

    /**
     * @When I view the pending submissions
     */
    public function iViewPendingSubmissions()
    {
        $this->clickLink('Pending');
    }

    /**
     * @Then the report PDF document should be queued
     */
    public function documentsAreSetToQueued()
    {
        $reportPrefix = 'ndr' === $this->interactingWithUserDetails->getCurrentReportNdrOrReport() ? 'NdrRep-' : 'DigiRep-';
        $this->assertRowWithStatusAppears($reportPrefix, 'Queued');
    }

    /**
     * @Then the document :filename should be queued
     */
    public function documentShouldBeQueued(string $fileName)
    {
        $this->assertRowWithStatusAppears($fileName, 'Queued');
    }

    /**
     * @Then the document :filename should be synced
     */
    public function documentShouldBeSynced(string $fileName)
    {
        $this->clickLink('Synchronised');

        $this->assertRowWithStatusAppears($fileName, 'Success');
    }

    /**
     * @Given /^I run the document\-sync command$/
     */
    public function iRunTheDocumentSyncCommand()
    {
        $this->visitAdminPath('/admin/behat/run-document-sync-command');

        if ($this->getSession()->getStatusCode() > 299) {
            throw new BehatException('There was an non successful response when running the document-sync command');
        }

        sleep(1);
    }

    /**
     * @Given /^the report PDF document should be synced$/
     */
    public function theReportPDFDocumentShouldBeSynced()
    {
        $this->assertRowWithStatusAppears('DigiRep-', 'Success');
    }

    /**
     * @When I attach a "second" supporting document :imageName to the :submitted report
     * @When I attach a supporting document :imageName to the :submitted report
     */
    public function attachSupportingDocumentToSubmittedReport(string $imageName, $reportStatus)
    {
        $this->iVisitTheDocumentsStep2Page();
        $this->attachDocument($imageName);

        if ('submitted' != $reportStatus) {
            $this->clickLink('Continue to send documents');
        }

        $this->clickLink('Send documents');
    }

    /**
     * @When I search for submissions using the :whichNameSearched name of the clients with the same :whichNamesAreSame name
     */
    public function iSearchForSubmissionsUsingTheFirstNameOfTheClientsWithTheSameFirstName(
        string $whichNameSearched,
        string $whichNamesAreSame,
    ) {
        $userDetails = 'first' === $whichNamesAreSame ? $this->sameFirstNameUserDetails[0] : $this->sameLastNameUserDetails[0];
        $nameToSearchOn = 'first' === $whichNameSearched ? $userDetails->getClientFirstName() : $userDetails->getClientLastName();

        $this->fillInField('q', $nameToSearchOn);
        $this->pressButton('Search');
        $this->clickLink('Pending');
    }

    /**
     * @Then I should see the clients with the same :whichName names in the search results
     */
    public function iShouldSeeBothClientsInTheSearchResults(string $whichName)
    {
        $usersToSearchOn = 'first' === $whichName ? $this->sameFirstNameUserDetails : $this->sameLastNameUserDetails;
        $locator = sprintf(
            '//td[normalize-space()="%s"]|//td[normalize-space()="%s"]',
            $usersToSearchOn[0]->getClientCaseNumber(),
            $usersToSearchOn[1]->getClientCaseNumber(),
        );

        $clientRows = $this->getSession()->getPage()->findAll('xpath', $locator);

        $this->assertIntEqualsInt(
            2,
            count($clientRows),
            sprintf('Count rows that contain case numbers of clients that have the same %s name', $whichName)
        );
    }

    /**
     * @Then I should not see the two clients with different :whichName names
     */
    public function iShouldNotSeeTheOtherTwoClientsWithDifferentNames(string $whichName)
    {
        $usersToSearchOn = 'first' === $whichName ? $this->sameFirstNameUserDetails : $this->sameLastNameUserDetails;
        $locator = sprintf(
            '//td[normalize-space()="%s"]|//td[normalize-space()="%s"]',
            $usersToSearchOn[0]->getClientCaseNumber(),
            $usersToSearchOn[1]->getClientCaseNumber(),
        );

        $clientRows = $this->getSession()->getPage()->findAll('xpath', $locator);

        $this->assertIntEqualsInt(
            0,
            count($clientRows),
            sprintf('Count rows that contain case numbers of clients that have the same %s name', $whichName)
        );
    }

    /**
     * @When I search for submissions using the court order number of the client with :numberReports report(s)
     */
    public function iSearchForSubmissionsUsingTheCourtOrderNumberOfTheClientWithNumberReports(string $numberReports)
    {
        $userToSearchOn = 'one' === $numberReports ? $this->oneReportsUserDetails : $this->twoReportsUserDetails;
        $this->fillInField('q', $userToSearchOn->getClientCaseNumber());
        $this->pressButton('Search');
        $this->clickLink('Pending');
    }

    /**
     * @Then I should see :numberRows rows for the client with :numberReports report submission(s) in the search results
     */
    public function iShouldSeeNumberRowsForClientWithNumberReports(string $numberRows, string $numberReports)
    {
        $userToSearchOn = 'one' === $numberReports ? $this->oneReportsUserDetails : $this->twoReportsUserDetails;
        $locator = sprintf(
            '//td[normalize-space()="%s"]',
            $userToSearchOn->getClientCaseNumber()
        );

        $clientRows = $this->getSession()->getPage()->findAll('xpath', $locator);

        $expectedRows = 'one' === $numberRows ? 1 : 2;
        $this->assertIntEqualsInt(
            $expectedRows,
            count($clientRows),
            sprintf('Count rows that contain case numbers of clients that has submitted %s reports', $numberReports)
        );
    }

    /**
     * @Then I should not see the client with :numberReports report submission(s) in the search results
     */
    public function iShouldNotSeeTheClientWithSubmissionsInResults(string $numberReports)
    {
        $userToSearchOn = 'one' === $numberReports ? $this->oneReportsUserDetails : $this->twoReportsUserDetails;

        $locator = sprintf(
            '//td[normalize-space()="%s"]',
            $userToSearchOn->getClientCaseNumber()
        );

        $clientRows = $this->getSession()->getPage()->findAll('xpath', $locator);

        $this->assertIntEqualsInt(
            0,
            count($clientRows),
            sprintf('Count rows that contain case numbers of clients that has submitted %s reports', $numberReports)
        );
    }

    /**
     * @When I manually :action the client that has one submitted report
     */
    public function iManuallyArchiveTheClientThatHasOneSubmittedReport(string $action)
    {
        $locator = sprintf(
            '//td[normalize-space()="%s"]/..//input',
            $this->oneReportsUserDetails->getClientCaseNumber()
        );

        $clientRowCheckBox = $this->getSession()->getPage()->find('xpath', $locator);
        $clientRowCheckBox->check();
        $this->pressButton('archive' === $action ? 'Archive' : 'Synchronise');
    }

    /**
     * @Then I should see the client row under the Synchronised tab
     */
    public function iShouldSeeTheClientRowUnderTheSynchronisedTab()
    {
        $this->clickLink('Synchronised');
        $this->iShouldSeeNumberRowsForClientWithNumberReports('one', 'one');
    }

    /**
     * @Given there was an error during synchronisation
     */
    public function thereWasAnErrorDuringSync()
    {
        $submittedReportId = $this->oneReportsUserDetails->getPreviousReportId();
        $submission = $this->em->getRepository(ReportSubmission::class)->findOneBy(['report' => $submittedReportId]);

        foreach ($submission->getDocuments() as $document) {
            $document->setSynchronisationStatus(Document::SYNC_STATUS_PERMANENT_ERROR);
            $this->documentFileNames[] = $document->getFilename();
            $this->em->persist($document);
        }

        $this->em->flush();
    }

    /**
     * @Then the status of the documents for the client with one report submission should be :status
     */
    public function statusOfSubmissionDocumentsShouldBe(string $status)
    {
        $locator = sprintf(
            '//td[normalize-space()="%s"]/../..',
            $this->oneReportsUserDetails->getClientCaseNumber()
        );

        $submissionRowTableBody = $this->getSession()->getPage()->find('xpath', $locator);

        foreach ($this->documentFileNames as $documentFileName) {
            $locator = sprintf(
                '//td[normalize-space()="%s"]/..',
                $documentFileName
            );

            $documentRow = $submissionRowTableBody->find('xpath', $locator);

            if (is_null($documentRow)) {
                $errorMessage = sprintf(
                    'Could not find a row that contained the status "%s" for submission with court order number "%s". Table HTML: %s',
                    $status,
                    $this->oneReportsUserDetails->getClientCaseNumber(),
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

    /**
     * @Given /^the document sync enabled flag is set to \'([^\']*)\'$/
     */
    public function theDocumentSyncEnabledFlagIsSetTo($documentFeatureFlagValue)
    {
        $this->parameterStoreService->putFeatureFlag(ParameterStoreService::FLAG_DOCUMENT_SYNC, $documentFeatureFlagValue);
    }

    /**
     * @Then /^the \'([^\']*)\' tab \'([^\']*)\' visible$/
     */
    public function tabVisibilityCheck($tabName, $visibility)
    {
        $shouldBeVisible = 'is' === $visibility;
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

    /**
     * @When I search for submissions using the court order number of the client I am interacting with and check the :status column
     */
    public function iSearchForSubmissionsUsingTheCourtOrderNumberOfTheClientIAmInteractingWithForTheStatusColumn(string $status)
    {
        $this->fillInField('q', $this->interactingWithUserDetails->getClientCaseNumber());
        $this->pressButton('Search');
        $this->clickLink($status);
    }

    /**
     * @Then I should not see the submission under the :status tab with the court order number of the user I am interacting with
     * @Then I should see the submission under the :status tab with the court order number of the user I am interacting with
     */
    public function submissionBehaviourBasedOnStatus(string $status)
    {
        $caseNumber = $this->interactingWithUserDetails->getClientCaseNumber();
        $reportPdfRow = $this->getSession()->getPage()->find('css', "table tr:contains('$caseNumber')");

        if ('New' === $status) {
            if (!is_null($reportPdfRow)) {
                throw new BehatException("The submission ($caseNumber) appears in the new column when it should not appear");
            }
        } elseif ('Pending' === $status) {
            if (is_null($reportPdfRow)) {
                throw new BehatException("The submission ($caseNumber) does not appear in the pending column when it should appear");
            }
        }
    }

    private function assertRowWithStatusAppears(string $searchTerm, string $status)
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

    /**
     * @Then I should see Lay High Assets report for the next reporting period
     */
    public function iShouldSeeLayHighAssetsReportForTheNextReportingPeriod()
    {
        $this->clickLink('Continue');
        $this->assertStringContainsString(
            'Money transfers',
            $this->getSession()->getPage()->getContent(),
            'Comparing expected section against sections visible');
    }

    /**
     * @Given /^the user uploaded a document with a file type that can be converted before the document conversion feature was released$/
     */
    public function theUserUploadedADocumentWithAFileTypeThatCanBeConvertedBeforeTheDocumentConversionFeatureWasReleased()
    {
        $this->iViewDocumentsSection();
        $this->iHaveDocumentsToUpload();
        $this->iAttachedASupportingDocumentToTheCompletedReport('good-heic.heic');

        // Have to hack in uploading a heic doc as the app now automatically converts type on adding documents
        $report = $this->em->getRepository(Report::class)->find($this->loggedInUserDetails->getCurrentReportId());
        $this->em->refresh($report);
        $document = $report->getDeputyDocuments()->first();

        $document->setFileName('good-heic.heic');

        $this->em->persist($document);
        $this->em->flush();

        $this->iSubmitTheReport();
    }
}
