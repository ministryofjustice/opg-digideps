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
     * @When /^the Lay deputy navigates to client details page$/
     */
    public function theLayDeputyNavigatesToTheClientDetailsPage(): void
    {
        $this->clickLink('Client details');
        $this->iAmOnClientDetailsPage();
    }
}
