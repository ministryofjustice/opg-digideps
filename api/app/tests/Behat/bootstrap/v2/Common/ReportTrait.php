<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\Common;

use OPG\Digideps\Backend\Entity\Report\Report;
use Tests\OPG\Digideps\Backend\Behat\BehatException;

trait ReportTrait
{
    public string $reportUrlPrefix = 'report';

    private string $reportAlreadySubmittedError = 'Report has already been submitted';

    /**
     * @Then I should be able to submit my :currentOrPrevious report without completing the client benefits check section
     *
     * @Given I follow the submission process to the declaration page for :currentOrPrevious report
     */
    public function iSubmitCurrentOrPreviousTheReport(string $currentOrPrevious): void
    {
        $reportId = 'current' === $currentOrPrevious
            ? $this->loggedInUserDetails->getCurrentReportId()
            : $this->loggedInUserDetails->getPreviousReportId();

        $this->visit("report/$reportId/overview");

        try {
            $this->clickLink('Preview and check report');
        } catch (\Exception) {
            try {
                $this->clickLink('Review and submit');
            } catch (\Exception) {
                $this->clickLink('Continue');
            }
        }

        $this->clickLink('Confirm contact details');
        $this->clickLink('Continue to declaration');
    }

    /**
     * @Given /^I fill in the declaration page and submit the report$/
     * @Given /^I fill in the declaration page and submit the report for a 2nd time$/
     */
    public function iFillInTheDeclarationPageAndSubmitTheReport(): void
    {
        $this->checkOption('report_declaration[agree]');
        $this->selectOption('report_declaration[agreedBehalfDeputy]', 'only_deputy');
        $this->pressButton('report_declaration[save]');
    }

    /**
     * @Then /^I can go back to the contact details page$/
     */
    public function iCanGoBackToTheContactDetailsPage(): void
    {
        $this->clickLink('Go back');
        $this->iAmOnReportConfirmDetailsPage();
        $this->assertPageContainsText('Confirm Your Contact Details');
    }

    /**
     * @Given /^I can go back to the report review page$/
     */
    public function iCanGoBackToTheReportReviewPage(): void
    {
        $this->clickLink('Go back');
        $this->iAmOnReportReviewPage();
        $this->assertPageContainsText('Check your report');
    }

    /**
     * @Given /^I can go back to the report overview page$/
     */
    public function iCanGoBackToTheReportOverviewPage(): void
    {
        $this->clickLink('Go back');
        $this->iAmOnReportsOverviewPage();
        $this->assertPageContainsText('Preview and check report');
    }

    /**
     * @Given a Lay Deputy has not started a report
     * @Given a Lay Deputy has not started a Pfa High Assets report
     * @Given a Lay Deputy logs in again
     */
    public function aLayDeputyHasNotStartedAReport(): void
    {
        if (empty($this->layDeputyNotStartedPfaHighAssetsDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $layDeputyNotStartedPfaHighAssetsDetails');
        }

        $this->loginToFrontendAs($this->layDeputyNotStartedPfaHighAssetsDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->layDeputyNotStartedPfaHighAssetsDetails;
    }

    /**
     * @Given a Lay Deputy has a completed report
     * @Given a Lay Deputy has completed a Pfa High Assets report
     *
     * @throws \Exception
     */
    public function aLayDeputyHasCompletedReport(): void
    {
        if (empty($this->layDeputyCompletedPfaHighAssetsDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $layDeputyCompletedPfaHighAssetsDetails');
        }

        $this->loginToFrontendAs($this->layDeputyCompletedPfaHighAssetsDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->layDeputyCompletedPfaHighAssetsDetails;
    }

    /**
     * @Given a Lay Deputy has a completed report with no client details
     *
     * @throws \Exception
     */
    public function aLayDeputyHasCompletedReportWithNoClientDetails(): void
    {
        if (empty($this->layDeputyCompletedPfaHighAssetsDetailsNoClientDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $layDeputyCompletedPfaHighAssetsDetailsNoClientDetails');
        }

        $this->loginToFrontendAs($this->layDeputyCompletedPfaHighAssetsDetailsNoClientDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->layDeputyCompletedPfaHighAssetsDetailsNoClientDetails;
    }

    /**
     * @Given a Lay Deputy has submitted a report
     * @Given a Lay Deputy has submitted a Pfa High Assets report
     *
     * @throws \Exception
     */
    public function aLayDeputyHasSubmittedAReport(): void
    {
        if (empty($this->layDeputySubmittedPfaHighAssetsDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $layDeputySubmittedPfaHighAssetsDetails');
        }

        $this->loginToFrontendAs($this->layDeputySubmittedPfaHighAssetsDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->layDeputySubmittedPfaHighAssetsDetails;
    }

    /**
     * @Given a Lay Deputy has submitted a health and welfare report
     *
     * @throws \Exception
     */
    public function aLayDeputyHasSubmittedAHealthAndWelfareReport(): void
    {
        if (empty($this->layDeputySubmittedHealthWelfareDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $layDeputySubmittedHealthWelfareDetails');
        }

        $this->loginToFrontendAs($this->layDeputySubmittedHealthWelfareDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->layDeputySubmittedHealthWelfareDetails;
    }

    /**
     * @Given a Professional Admin Deputy has not started a report
     */
    public function aProfessionalAdminDeputyHasNotStartedAReport(): void
    {
        if (empty($this->profAdminDeputyHealthWelfareNotStartedDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $profAdminDeputyHealthWelfareNotStartedDetails');
        }

        $this->loginToFrontendAs($this->profAdminDeputyHealthWelfareNotStartedDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->profAdminDeputyHealthWelfareNotStartedDetails;
    }

    /**
     * @Given a Public Authority Admin Deputy has not started a report
     */
    public function aPublicAuthorityAdminDeputyHasNotStartedAReport(): void
    {
        if (empty($this->paAdminDeputyNotStartedDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $paAdminDeputyNotStartedDetails');
        }

        $this->loginToFrontendAs($this->paAdminDeputyNotStartedDetails->getUserEmail());
    }

    /**
     * @Given a Professional Team Deputy has not started a health and welfare report
     */
    public function aProfessionalHealthWelfareDeputyHasNotStartedAReport(): void
    {
        if (empty($this->profTeamDeputyNotStartedHealthWelfareDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $profTeamDeputyNotStartedHealthWelfareDetails');
        }

        $this->loginToFrontendAs($this->profTeamDeputyNotStartedHealthWelfareDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->profTeamDeputyNotStartedHealthWelfareDetails;
    }

    /**
     * @Given a Professional Team Deputy has completed a health and welfare report
     */
    public function aProfessionalHealthWelfareDeputyHasCompletedAReport(): void
    {
        if (empty($this->profTeamDeputyCompletedHealthWelfareDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $profTeamDeputyCompletedHealthWelfareDetails');
        }

        $this->loginToFrontendAs($this->profTeamDeputyCompletedHealthWelfareDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->profTeamDeputyCompletedHealthWelfareDetails;
    }

    /**
     * @Given a Lay Deputy has not started a Pfa Low Assets report
     */
    public function aLayDeputyHasNotStartedAPfaLowAssetsReport(): void
    {
        if (empty($this->layDeputyNotStartedPfaLowAssetsDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $layDeputyNotStartedPfaLowAssetsDetails');
        }

        $this->loginToFrontendAs($this->layDeputyNotStartedPfaLowAssetsDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->layDeputyNotStartedPfaLowAssetsDetails;
    }

    /**
     * @Given a Professional Admin has not started a Pfa Low Assets report
     */
    public function aProfDeputyHasNotStartedAPfaLowAssetsReport(): void
    {
        if (empty($this->profAdminDeputyNotStartedPfaLowAssetsDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $profAdminDeputyNotStartedPfaLowAssetsDetails');
        }

        $this->loginToFrontendAs($this->profAdminDeputyNotStartedPfaLowAssetsDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->profAdminDeputyNotStartedPfaLowAssetsDetails;
    }

    /**
     * @Given a Lay Deputy has completed a Pfa Low Assets report
     */
    public function aLayDeputyHasCompletedAPfaLowAssetsReport(): void
    {
        if (empty($this->layDeputyCompletedPfaLowAssetsDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $layDeputyCompletedPfaLowAssetsDetails');
        }

        $this->loginToFrontendAs($this->layDeputyCompletedPfaLowAssetsDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->layDeputyCompletedPfaLowAssetsDetails;
    }

    /**
     * @Given a Professional Admin has completed a Pfa Low Assets report
     */
    public function aProfAdminDeputyHasCompletedAPfaLowAssetsReport(): void
    {
        if (empty($this->profAdminDeputyCompletedPfaLowAssetsDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $profAdminDeputyCompletedPfaLowAssetsDetails');
        }

        $this->loginToFrontendAs($this->profAdminDeputyCompletedPfaLowAssetsDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->profAdminDeputyCompletedPfaLowAssetsDetails;
    }

    /**
     * @Given a Lay Deputy has not started a Health and Welfare report
     */
    public function aLayDeputyHasNotStartedAHealthWelfareReport(): void
    {
        if (empty($this->layDeputyNotStartedHealthWelfareDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $layDeputyNotStartedHealthWelfareDetails');
        }

        $this->loginToFrontendAs($this->layDeputyNotStartedHealthWelfareDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->layDeputyNotStartedHealthWelfareDetails;
    }

    /**
     * @Given a Lay Deputy has completed a Health and Welfare report
     */
    public function aLayDeputyHasCompletedAHealthWelfareReport(): void
    {
        if (empty($this->layDeputyCompletedHealthWelfareDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $layDeputyCompletedHealthWelfareDetails');
        }

        $this->loginToFrontendAs($this->layDeputyCompletedHealthWelfareDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->layDeputyCompletedHealthWelfareDetails;
    }

    /**
     * @Given a Lay Deputy has not started a Combined High Assets report
     */
    public function aLayDeputyHasNotStartedACombinedHighAssetsReport(): void
    {
        if (empty($this->layDeputyNotStartedCombinedHighDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $layDeputyNotStartedCombinedHighDetails');
        }

        $this->interactingWithUserDetails = $this->layDeputyNotStartedCombinedHighDetails;
        $this->loginToFrontendAs($this->layDeputyNotStartedCombinedHighDetails->getUserEmail());
    }

    /**
     * @Given a Lay Deputy has completed a Combined High Assets report
     */
    public function aLayDeputyHasCompletedACombinedHighAssetsReport(): void
    {
        if (empty($this->layDeputyCompletedCombinedHighDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $layDeputyCompletedCombinedHighDetails');
        }

        $this->interactingWithUserDetails = $this->layDeputyCompletedCombinedHighDetails;
        $this->loginToFrontendAs($this->layDeputyCompletedCombinedHighDetails->getUserEmail());
    }

    /**
     * @Given a Lay Deputy has submitted a Combined High Assets report
     */
    public function aLayDeputyHasSubmittedACombinedHighAssetsReport(): void
    {
        if (empty($this->layDeputySubmittedCombinedHighDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $layDeputySubmittedCombinedHighDetails');
        }

        $this->loginToFrontendAs($this->layDeputySubmittedCombinedHighDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->layDeputySubmittedCombinedHighDetails;
    }

    /**
     * @Given a Professional Deputy has submitted a Health and Welfare report
     * @Given a Professional Deputy has submitted a report
     *
     * @throws BehatException
     */
    public function aProfessionalDeputyHasSubmittedAReport(): void
    {
        if (empty($this->profAdminDeputyHealthWelfareSubmittedDetails)) {
            throw new BehatException('It looks like fixtures are not loaded - missing $profAdminDeputySubmittedDetails');
        }

        $this->loginToFrontendAs($this->profAdminDeputyHealthWelfareSubmittedDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->profAdminDeputyHealthWelfareSubmittedDetails;
    }

    /**
     * @Given a Public Authority Deputy has submitted a Health and Welfare report
     *
     * @throws BehatException
     */
    public function aPublicAuthorityDeputyHasSubmittedAReport(): void
    {
        if (empty($this->publicAuthorityNamedDeputySubmittedDetails)) {
            throw new BehatException('It looks like fixtures are not loaded - missing $publicAuthorityNamedDeputySubmittedDetails');
        }

        $this->loginToFrontendAs($this->publicAuthorityNamedDeputySubmittedDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->publicAuthorityNamedDeputySubmittedDetails;
    }

    /**
     * @Given a Professional Deputy has completed a Pfa Low Assets report
     * @Given a Professional Deputy has completed a report
     */
    public function aProfDeputyHasCompletedAPfaLowAssetsReport(): void
    {
        if (empty($this->profAdminDeputyHealthWelfareCompletedDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $profAdminDeputyCompletedDetails');
        }

        $this->loginToFrontendAs($this->profAdminDeputyHealthWelfareCompletedDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->profAdminDeputyHealthWelfareCompletedDetails;
    }

    /**
     * @Given a Professional Deputy has not started a Pfa High Assets report
     */
    public function aProfDeputyHasNotStartedAPfaHighAssetsReport(): void
    {
        if (empty($this->profNamedDeputyNotStartedPfaHighDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $profNamedDeputyNotStartedPfaHighDetails');
        }

        $this->loginToFrontendAs($this->profNamedDeputyNotStartedPfaHighDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->profNamedDeputyNotStartedPfaHighDetails;
    }

    /**
     * @Given a Professional Deputy has submitted a Pfa High Assets report
     */
    public function aProfDeputyHasSubmittedAPfaHighAssetsReport(): void
    {
        if (empty($this->profNamedDeputySubmittedPfaHighDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $profNamedDeputySubmittedPfaHighDetails');
        }

        $this->loginToFrontendAs($this->profNamedDeputySubmittedPfaHighDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->profNamedDeputySubmittedPfaHighDetails;
    }

    /**
     * @Given a Public Authority Deputy has not started a Combined High Assets report
     */
    public function aPublicAuthorityDeputyHasNotStartedACombinedHighAssetsReport(): void
    {
        if (empty($this->publicAuthorityAdminCombinedHighNotStartedDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $publicAuthorityAdminCombinedHighNotStartedDetails');
        }

        $this->loginToFrontendAs($this->publicAuthorityAdminCombinedHighNotStartedDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->publicAuthorityAdminCombinedHighNotStartedDetails;
    }

    /**
     * @Given a Public Authority Deputy has submitted a Combined High Assets report
     */
    public function aPublicAuthorityDeputyHasSubmittedACombinedHighAssetsReport(): void
    {
        if (empty($this->publicAuthorityAdminCombinedHighSubmittedDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $publicAuthorityAdminCombinedHighSubmittedDetails');
        }

        $this->loginToFrontendAs($this->publicAuthorityAdminCombinedHighSubmittedDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->publicAuthorityAdminCombinedHighSubmittedDetails;
    }

    /**
     * @Given a Public Authority Named Deputy has not started a Pfa High Assets report
     */
    public function aPublicAuthorityNamedDeputyHasNotStartedAPfaHighAssetsReport(): void
    {
        if (empty($this->publicAuthorityNamedNotStartedPfaHighDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $publicAuthorityNamedNotStartedPfaHighDetails');
        }

        $this->loginToFrontendAs($this->publicAuthorityNamedNotStartedPfaHighDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->publicAuthorityNamedNotStartedPfaHighDetails;
    }

    /**
     * @Given a Public Authority Named Deputy has submitted a Pfa High Assets report
     */
    public function aPublicAuthorityNamedDeputyHasSubmittedAPfaHighAssetsReport(): void
    {
        if (empty($this->publicAuthorityNamedSubmittedPfaHighDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $publicAuthorityNamedSubmittedPfaHighDetails');
        }

        $this->loginToFrontendAs($this->publicAuthorityNamedSubmittedPfaHighDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->publicAuthorityNamedSubmittedPfaHighDetails;
    }

    /**
     * @Given a Professional Admin Deputy has not Started a Combined High Assets report
     */
    public function aProfAdminHasNotStartedACombinedHighAssetsReport(): void
    {
        if (empty($this->profAdminCombinedHighNotStartedDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $profAdminCombinedHighNotStartedDetails');
        }

        $this->interactingWithUserDetails = $this->profAdminCombinedHighNotStartedDetails;
        $this->loginToFrontendAs($this->profAdminCombinedHighNotStartedDetails->getUserEmail());
    }

    /**
     * @Given a Professional Admin Deputy has completed a Combined High Assets report
     */
    public function aProfAdminHasCompletedStartedACombinedHighAssetsReport(): void
    {
        if (empty($this->profAdminCombinedHighCompletedDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $profAdminCombinedHighCompletedDetails');
        }

        $this->interactingWithUserDetails = $this->profAdminCombinedHighCompletedDetails;
        $this->loginToFrontendAs($this->profAdminCombinedHighCompletedDetails->getUserEmail());
    }

    /**
     * @Given a Professional Admin Deputy has submitted a Combined High Assets report
     */
    public function aProfAdminHasSubmittedACombinedHighAssetsReport(): void
    {
        if (empty($this->profAdminCombinedHighSubmittedDetails)) {
            throw new \Exception('It looks like fixtures are not loaded - missing $profAdminCombinedHighSubmittedDetails');
        }

        $this->interactingWithUserDetails = $this->profAdminCombinedHighSubmittedDetails;
        $this->loginToFrontendAs($this->profAdminCombinedHighSubmittedDetails->getUserEmail());
    }

    /**
     * @Given the end date and due date of the logged in users :currentOrPrevious report is set to :dateString
     */
    public function endDateAndDueDateLoggedInUsersCurrentReportSetToDate(string $dateString, string $currentOrPrevious): void
    {
        if (
            empty($this->loggedInUserDetails)
            || (empty($this->loggedInUserDetails->getCurrentReportId()) && empty($this->loggedInUserDetails->getPreviousReportId()))
        ) {
            throw new BehatException('The logged in user does not have a report. Ensure a user with a report has logged in before using this step.');
        }

        if (!in_array($currentOrPrevious, ['current', 'previous'])) {
            throw new BehatException('This step only supports "current" and "previous" as arguments for $currentOrPrevious. Either add to the step or use an available option.');
        }

        $newDate = new \DateTime($dateString);

        $reportIdToUpdate = 'current' === $currentOrPrevious ? $this->loggedInUserDetails->getCurrentReportId() : $this->loggedInUserDetails->getPreviousReportId();

        /** @var Report $reportToUpdate */
        $reportToUpdate = $this->em->getRepository(Report::class)->find($reportIdToUpdate);
        $reportToUpdate->setEndDate($newDate);
        $reportToUpdate->setDueDate($newDate);

        $this->em->persist($reportToUpdate);
        $this->em->flush();

        if ('current' === $currentOrPrevious) {
            $this->loggedInUserDetails->setCurrentReportDueDate($newDate);
            $this->loggedInUserDetails->setCurrentReportEndDate($newDate);
        } else {
            $this->loggedInUserDetails->setPreviousReportDueDate($newDate);
            $this->loggedInUserDetails->setPreviousReportEndDate($newDate);
        }
    }

    /**
     * @When /^I preview and check the report$/
     */
    public function iPreviewAndCheckTheReport(): void
    {
        $this->pressButton('Preview and check report');
        $this->iAmOnReportReviewPage();
    }

    /**
     * @Given /^I continue to declaration and submission$/
     */
    public function iContinueToDeclarationAndSubmission(): void
    {
        $this->clickLink('Confirm contact details');
        $this->iAmOnReportConfirmDetailsPage();
        $this->clickLink('Continue to declaration');
        $this->iAmOnReportDeclarationPage();
    }

    /**
     * @Given /^I confirm I agree to the declaration$/
     */
    public function iConfirmIAgreeToTheDeclaration(): void
    {
        $this->checkOption('report_declaration[agree]');
    }

    /**
     * @Given /^I confirm I am the sole deputy$/
     */
    public function iConfirmIAmTheSoleDeputy(): void
    {
        $this->selectOption('report_declaration[agreedBehalfDeputy]', 'only_deputy');
    }

    /**
     * @Given /^I submit my report$/
     */
    public function iSubmitMyReport(): void
    {
        $this->pressButton('Submit report');
        $this->iAmOnReportSubmittedPage();
    }

    /**
     * @Then /^my report should be submitted$/
     */
    public function myReportShouldBeSubmitted(): void
    {
        $this->assertPageContainsText('Your report has been sent to OPG');
    }

    /**
     * @Then I should see :numberOfReports report(s)
     */
    public function iShouldSeeNumberOfReports(int $expectedNumberOfReports): void
    {
        $links = $this->getSession()->getPage()->findAll('css', 'a');

        $reportLinks = [];

        foreach ($links as $link) {
            if (preg_match('/report\/[0-9]+\/.*view/', $link->getAttribute('href')) && !in_array($link->getAttribute('href'), $reportLinks)) {
                $reportLinks[] = $link->getAttribute('href');
            }
        }

        $numLinks = count($reportLinks);
        if ($numLinks != $expectedNumberOfReports) {
            $message = $this->getAssertMessage(
                "$expectedNumberOfReports",
                "$numLinks",
                'Found a different number of reports than expected'
            );

            assert(false, $message);
        }
    }

    /**
     * @When I change the current report status to :status
     *
     * @throws \Exception
     */
    public function aLayDeputyHasTheirCurrentReportStatusChanged(string $status): void
    {
        $reportId = $this->loggedInUserDetails->getCurrentReportId();

        /** @var Report $report */
        $report = $this->em->getRepository(Report::class)->find($reportId);
        if (!$report) {
            throw new \Exception("Report with ID $reportId not found.");
        }

        if ($status === 'ready to submit') {
            $report->setSubmitted(false);
        } elseif ($status === 'submitted') {
            $report->setSubmitted(true);
        } else {
            throw new \Exception("Unsupported status: $status. Supported statuses are 'ready to submit' and 'submitted'.");
        }

        $this->em->persist($report);
        $this->em->flush();
    }

    /**
     * @Then I should see an error message that the report has already been submitted
     *
     * @throws \Exception
     */
    public function aLayDeputySeesAnErrorMessageWhenSubmittingASubmittedReport(): void
    {
        $this->assertOnErrorMessage($this->reportAlreadySubmittedError);
    }
}
