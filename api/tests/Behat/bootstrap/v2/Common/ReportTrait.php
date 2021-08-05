<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

use App\Tests\Behat\BehatException;
use Exception;

trait ReportTrait
{
    public string $reportUrlPrefix = 'report';

    /**
     * @Given /^I submit the report$/
     */
    public function iSubmitTheReport()
    {
        $ndrOrReport = $this->loggedInUserDetails->getCurrentReportNdrOrReport();
        $reportId = $this->loggedInUserDetails->getCurrentReportId();

        $this->visit("$ndrOrReport/$reportId/overview");

        try {
            $this->clickLink('Preview and check report');
        } catch (Exception $e) {
            $this->clickLink('Continue');
        }

        $this->clickLink('Continue');

        $this->checkOption(sprintf('%s_declaration[agree]', $ndrOrReport));
        $this->selectOption(sprintf('%s_declaration[agreedBehalfDeputy]', $ndrOrReport), 'only_deputy');
        $this->pressButton(sprintf('%s_declaration[save]', $ndrOrReport));
    }

    /**
     * @Given a Lay Deputy has not started a report
     * @Given a Lay Deputy has not started a Pfa High Assets report
     */
    public function aLayDeputyHasNotStartedAReport()
    {
        if (empty($this->layDeputyNotStartedPfaHighAssetsDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $layDeputyNotStartedPfaHighAssetsDetails');
        }

        $this->loginToFrontendAs($this->layDeputyNotStartedPfaHighAssetsDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->layDeputyNotStartedPfaHighAssetsDetails;
    }

    /**
     * @Given a Lay Deputy has a completed report
     * @Given a Lay Deputy has completed a Pfa High Assets report
     *
     * @throws Exception
     */
    public function aLayDeputyHasCompletedReport()
    {
        if (empty($this->layDeputyCompletedPfaHighAssetsDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $layDeputyCompletedPfaHighAssetsDetails');
        }

        $this->loginToFrontendAs($this->layDeputyCompletedPfaHighAssetsDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->layDeputyCompletedPfaHighAssetsDetails;
    }

    /**
     * @Given a Lay Deputy has not started an NDR report
     */
    public function aNdrLayDeputyHasNotStartedAReport()
    {
        if (empty($this->layNdrDeputyNotStartedDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $layNdrDeputyNotStartedDetails');
        }

        $this->loginToFrontendAs($this->layNdrDeputyNotStartedDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->layNdrDeputyNotStartedDetails;
        $this->reportUrlPrefix = $this->layNdrDeputyNotStartedDetails->getCurrentReportNdrOrReport();
    }

    /**
     * @Given a Lay Deputy has a completed NDR report
     *
     * @throws Exception
     */
    public function aNdrLayDeputyHasCompletedReport()
    {
        if (empty($this->layNdrDeputyCompletedDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $layNdrDeputyCompletedDetails');
        }

        $this->loginToFrontendAs($this->layNdrDeputyCompletedDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->layNdrDeputyCompletedDetails;
        $this->reportUrlPrefix = $this->layNdrDeputyCompletedDetails->getCurrentReportNdrOrReport();
    }

    /**
     * @Given a Lay Deputy has submitted a report
     *
     * @throws Exception
     */
    public function aLayDeputyHasSubmittedAReport()
    {
        if (empty($this->layDeputySubmittedPfaHighAssetsDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $layDeputySubmittedPfaHighAssetsDetails');
        }

        $this->loginToFrontendAs($this->layDeputySubmittedPfaHighAssetsDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->layDeputySubmittedPfaHighAssetsDetails;
    }

    /**
     * @Given a Professional Admin Deputy has not started a report
     */
    public function aProfessionalAdminDeputyHasNotStartedAReport()
    {
        if (empty($this->profAdminDeputyHealthWelfareNotStartedDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $profAdminDeputyNotStartedDetails');
        }

        $this->loginToFrontendAs($this->profAdminDeputyHealthWelfareNotStartedDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->profAdminDeputyHealthWelfareNotStartedDetails;
    }

    /**
     * @Given a Professional Team Deputy has not started a health and welfare report
     */
    public function aProfessionalHealthWelfareDeputyHasNotStartedAReport()
    {
        if (empty($this->profTeamDeputyNotStartedHealthWelfareDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $profTeamDeputyNotStartedHealthWelfareDetails');
        }

        $this->loginToFrontendAs($this->profTeamDeputyNotStartedHealthWelfareDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->profTeamDeputyNotStartedHealthWelfareDetails;
    }

    /**
     * @Given a Professional Team Deputy has completed a health and welfare report
     */
    public function aProfessionalHealthWelfareDeputyHasCompletedAReport()
    {
        if (empty($this->profTeamDeputyCompletedHealthWelfareDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $profTeamDeputyCompletedHealthWelfareDetails');
        }

        $this->loginToFrontendAs($this->profTeamDeputyCompletedHealthWelfareDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->profTeamDeputyCompletedHealthWelfareDetails;
    }

    /**
     * @Given a Lay Deputy has not started a Pfa Low Assets report
     */
    public function aLayDeputyHasNotStartedAPfaLowAssetsReport()
    {
        if (empty($this->layDeputyNotStartedPfaLowAssetsDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $layDeputyNotStartedPfaLowAssetsDetails');
        }

        $this->loginToFrontendAs($this->layDeputyNotStartedPfaLowAssetsDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->layDeputyNotStartedPfaLowAssetsDetails;
    }

    /**
     * @Given a Lay Deputy has completed a Pfa Low Assets report
     */
    public function aLayDeputyHasCompletedAPfaLowAssetsReport()
    {
        if (empty($this->layDeputyCompletedPfaLowAssetsDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $layDeputyCompletedPfaLowAssetsDetails');
        }

        $this->loginToFrontendAs($this->layDeputyCompletedPfaLowAssetsDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->layDeputyCompletedPfaLowAssetsDetails;
    }

    /**
     * @Given a Lay Deputy has not started a Health and Welfare report
     */
    public function aLayDeputyHasNotStartedAHealthWelfareReport()
    {
        if (empty($this->layDeputyNotStartedHealthWelfareDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $layDeputyNotStartedHealthWelfareDetails');
        }

        $this->loginToFrontendAs($this->layDeputyNotStartedHealthWelfareDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->layDeputyNotStartedHealthWelfareDetails;
    }

    /**
     * @Given a Lay Deputy has completed a Health and Welfare report
     */
    public function aLayDeputyHasCompletedAHealthWelfareReport()
    {
        if (empty($this->layDeputyCompletedHealthWelfareDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $layDeputyCompletedHealthWelfareDetails');
        }

        $this->loginToFrontendAs($this->layDeputyCompletedHealthWelfareDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->layDeputyCompletedHealthWelfareDetails;
    }

    /**
     * @Given a Lay Deputy has submitted a Combined High Assets report
     */
    public function aLayDeputyHasSubmittedACombinedHighAssetsReport()
    {
        if (empty($this->layDeputySubmittedCombinedHighDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $layDeputySubmittedCombinedHighDetails');
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
    public function aProfessionalDeputyHasSubmittedAReport()
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
    public function aPublicAuthorityDeputyHasSubmittedAReport()
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
    public function aProfDeputyHasCompletedAPfaLowAssetsReport()
    {
        if (empty($this->profAdminDeputyHealthWelfareCompletedDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $profAdminDeputyCompletedDetails');
        }

        $this->loginToFrontendAs($this->profAdminDeputyHealthWelfareCompletedDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->profAdminDeputyHealthWelfareCompletedDetails;
    }

    /**
     * @Given a Public Authority Deputy has submitted a Combined High Assets report
     */
    public function aProfessionalDeputyHasSubmittedACombinedHighAssetsReport()
    {
        if (empty($this->publicAuthorityAdminCombinedHighSubmittedDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $publicAuthorityAdminCombinedHighSubmittedDetails');
        }

        $this->loginToFrontendAs($this->publicAuthorityAdminCombinedHighSubmittedDetails->getUserEmail());
        $this->interactingWithUserDetails = $this->publicAuthorityAdminCombinedHighSubmittedDetails;
    }
}
