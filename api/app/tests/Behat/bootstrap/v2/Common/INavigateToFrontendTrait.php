<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\Common;

trait INavigateToFrontendTrait
{
    /**
     * @When /^I navigate to my user settings page$/
     */
    public function iNavigateToMyUserSettingsPage(): void
    {
        $this->clickLink('Settings');
        $this->iAmOnOrgSettingsPage();
    }

    /**
     * @When /^the Lay deputy navigates to the report overview page$/
     */
    public function theLayDeputyNavigatesToTheReportOverviewPage(): void
    {
        $this->clickLink('Continue');
        $this->iAmOnReportsOverviewPage();
    }

    /**
     * @When the Lay Deputy navigates back to the Client dashboard using the breadcrumb
     */
    public function theLayDeputynavigatesBackToTheClientDashboardUsingTheBreadcrumb(): void
    {
        $this->clickBasedOnText($this->layPfaHighNotStartedMultiClientDeputyPrimaryUser->getClientFirstName());
    }

    /**
     * @When /^the Lay deputy navigates to your details page$/
     */
    public function theLayDeputyNavigatesToTheYourDetailsPage(): void
    {
        $this->clickLink('Your details');
        $this->iAmOnYourDetailsPage();
    }

    /**
     * @When /^the Lay deputy navigates to client details page$/
     */
    public function theLayDeputyNavigatesToTheClientDetailsPage(): void
    {
        $this->clickLink('Client details');
        $this->iAmOnClientDetailsPage();
    }
}
