<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

trait INavigateToFrontendTrait
{
    /**
     * @When /^I navigate to the upload further documents page$/
     */
    public function iNavigateToTheUploadFurtherDocumentsPage()
    {
        $this->clickLink('Attach documents');
        $this->iAmOnFurtherUploadDocumentsPage();
    }

    /**
     * @When /^I navigate to my user settings page$/
     */
    public function iNavigateToMyUserSettingsPage()
    {
        $this->clickLink('Settings');
        $this->iAmOnOrgSettingsPage();
    }

    /**
     * @When /^the Lay deputy navigates back to the Choose a Client homepage$/
     */
    public function theLayDeputyNavigatesBackToTheChooseAClientHomepage()
    {
        $this->clickLink('Complete the deputy report');
        $this->iAmOnChooseAClientMainPage();
    }

    /**
     * @When /^the Lay deputy navigates back to the Choose a Client homepage using the breadcrumb$/
     */
    public function theLayDeputyNavigatesBackToTheChooseAClientHomepageUsingBreadcrumb()
    {
        $this->clickLink('Choose a client');
        $this->iAmOnChooseAClientMainPage();
    }

    /**
     * @When /^the Lay deputy navigates to the report overview page$/
     */
    public function theLayDeputyNavigatesToTheReportOverviewPage()
    {
        $this->clickLink('Continue');
        $this->iAmOnReportsOverviewPage();
    }

    /**
     * @When the Lay Deputy navigates back to the Client dashboard using the breadcrumb
     */
    public function theLayDeputynavigatesBackToTheClientDashboardUsingTheBreadcrumb()
    {
        $this->clickBasedOnText($this->layPfaHighNotStartedMultiClientDeputyPrimaryUser->getClientFirstName());
    }

    /**
     * @When /^the Lay deputy navigates to your details page$/
     */
    public function theLayDeputyNavigatesToTheYourDetailsPage()
    {
        $this->clickLink('Your details');
        $this->iAmOnYourDetailsPage();
    }

    /**
     * @When /^the Lay deputy navigates to client details page$/
     */
    public function theLayDeputyNavigatesToTheClientDetailsPage()
    {
        $this->clickLink('Client details');
        $this->iAmOnClientDetailsPage();
    }
}
