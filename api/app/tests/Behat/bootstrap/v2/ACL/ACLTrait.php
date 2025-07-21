<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\ACL;

use App\Tests\Behat\BehatException;

trait ACLTrait
{
    /**
     * @Then I should be able to access the :page
     */
    public function iShouldBeAbleToAccessAnalyticsPage(string $page)
    {
        $linkText = 'Download %s';
        $this->assertLinkWithTextIsOnPage(sprintf($linkText, $page));

        $this->visitAnalyticsPage($page);

        $this->canAccessSensitivePage();
    }

    private function canAccessSensitivePage()
    {
        $this->assertIntEqualsInt(
            200,
            $this->getSession()->getStatusCode(),
            'Status code after accessing endpoint'
        );
    }

    private function canNotAccessSensitivePage()
    {
        $this->assertIntEqualsInt(
            403,
            $this->getSession()->getStatusCode(),
            'Status code after accessing endpoint'
        );
    }

    /**
     * @Then I should not be able to access the :page
     */
    public function iShouldNotBeAbleToAccessAnalyticsPage(string $page)
    {
        $this->iVisitAdminAnalyticsPage();

        $linkText = 'Download %s';
        $this->assertLinkWithTextIsNotOnPage(sprintf($linkText, $page));

        $this->visitAnalyticsPage($page);

        $this->canNotAccessSensitivePage();
    }

    private function visitAnalyticsPage(string $pageName)
    {
        $lowercasePageName = strtolower($pageName);

        switch ($lowercasePageName) {
            case 'dat file':
                $this->iVisitAdminDATReportPage();
                break;
            case 'satisfaction report':
                $this->iVisitAdminSatisfactionReportPage();
                break;
            case 'active lays report':
                $this->iVisitAdminActiveLaysPage();
                break;
            case 'user research report':
                $this->iVisitAdminUserResearchReportPage();
                break;
            case 'view reports':
                $this->iVisitAdminStatsReportsPage();
                break;
            case 'inactive admin users report':
                $this->iVisitInactiveAdminUsersReportsPage();
                break;
            default:
                throw new BehatException(sprintf('Analytics page "%s" unrecognised', $lowercasePageName));
        }
    }

    /**
     * @Then /^I should be able to access the \'([^\']*)\' page$/
     */
    public function iShouldBeAbleToAccessThePage(string $page)
    {
        $this->assertLinkWithTextIsOnPage($page);

        match (strtolower($page)) {
            'fixtures' => $this->iVisitAdminFixturesPage(),
            'notifications' => $this->iVisitTheAdminNotificationPage(),
            'tools' => $this->iVisitAdminToolsPage(),
        };

        $this->canAccessSensitivePage();
    }

    /**
     * @Then /^I should not be able to access the \'([^\']*)\' page$/
     */
    public function iShouldNotBeAbleToAccessThePage(string $page)
    {
        $this->assertLinkWithTextIsNotOnPage($page);

        match (strtolower($page)) {
            'fixtures' => $this->iVisitAdminFixturesPage(),
            'notifications' => $this->iVisitTheAdminNotificationPage(),
            'tools' => $this->iVisitAdminToolsPage(),
        };

        $this->canNotAccessSensitivePage();
    }
}
