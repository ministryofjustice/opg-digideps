<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\OrgDashboard;

use App\Entity\User;
use App\Tests\Behat\v2\ClientManagement\ClientManagementTrait;
use App\Tests\Behat\v2\Common\BaseFeatureContext;

class OrgDashboardFeatureContext extends BaseFeatureContext
{
    use ClientManagementTrait;

    /**
     * @Given a PA admin with email :email logs in to the frontend
     *
     * Create and login as the PA admin with the given email
     */
    public function aPaManagerLogsIn(string $email): void
    {
        $this->fixtureHelper->createAndPersistUser(User::ROLE_PA_ADMIN, $email);
        $this->loginToFrontendAs($email);
    }

    /**
     * @Given I should see :numReports reports on the org dashboard page
     * @Given I should see :numReports report on the org dashboard page
     *
     * Check reports shown on the /org dashboard page
     */
    public function iShouldSeeNReports(int $numReports): void
    {
        $this->iVisitOrgDashboard();
        $this->printLastResponse();
    }
}
