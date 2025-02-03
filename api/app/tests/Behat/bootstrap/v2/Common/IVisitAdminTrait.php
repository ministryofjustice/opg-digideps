<?php

namespace App\Tests\Behat\v2\Common;

use App\Entity\Organisation;
use App\Tests\Behat\BehatException;

trait IVisitAdminTrait
{
    /**
     * @When I visit the admin clients search page
     */
    public function iVisitAdminClientSearchPage()
    {
        if (!in_array($this->loggedInUserDetails->getUserRole(), $this->loggedInUserDetails::ADMIN_ROLES)) {
            throw new BehatException('Attempting to access an admin page as a non-admin user. Try logging in as an admin user instead');
        }

        $this->visitAdminPath($this->getAdminClientSearchUrl());
    }

    /**
     * @When I visit the admin client details page for an existing client linked to a Lay deputy
     */
    public function iVisitAdminLayClientDetailsPage()
    {
        if (!in_array($this->loggedInUserDetails->getUserRole(), $this->loggedInUserDetails::ADMIN_ROLES)) {
            throw new BehatException('Attempting to access an admin page as a non-admin user. Try logging in as an admin user instead');
        }

        $clientDetailsUrl = $this->getAdminClientDetailsUrl($this->layDeputySubmittedPfaHighAssetsDetails->getClientId());
        $this->visitAdminPath($clientDetailsUrl);

        $this->interactingWithUserDetails = $this->layDeputySubmittedPfaHighAssetsDetails;
    }

    /**
     * @When I visit the admin client details page for an existing client linked to a non-primary Lay deputy user account
     */
    public function iVisitAdminLayClientDetailsPageForNonPrimaryDeputy()
    {
        if (!in_array($this->loggedInUserDetails->getUserRole(), $this->loggedInUserDetails::ADMIN_ROLES)) {
            throw new BehatException('Attempting to access an admin page as a non-admin user. Try logging in as an admin user instead');
        }

        $clientDetailsUrl = $this->getAdminClientDetailsUrl($this->layPfaHighNotStartedMultiClientDeputyNonPrimaryUser->getClientId());
        $this->visitAdminPath($clientDetailsUrl);

        $this->interactingWithUserDetails = $this->layPfaHighNotStartedMultiClientDeputyNonPrimaryUser;
    }

    /**
     * @When I visit the admin client archived page for the user I'm interacting with
     */
    public function iVisitTheAdminClientArchivedPage()
    {
        if (!in_array($this->loggedInUserDetails->getUserRole(), $this->loggedInUserDetails::ADMIN_ROLES)) {
            throw new BehatException('Attempting to access an admin page as a non-admin user. Try logging in as an admin user instead');
        }

        $clientArchivedUrl = $this->getAdminClientArchivedUrl($this->interactingWithUserDetails->getClientId());
        $this->visitAdminPath($clientArchivedUrl);
    }

    /**
     * @When I visit the admin client details page associated with the deputy I'm interacting with
     */
    public function iVisitAdminClientDetailsPageForDeputyInteractingWith()
    {
        $this->assertInteractingWithUserIsSet();

        $clientDetailsUrl = $this->getAdminClientDetailsUrl($this->interactingWithUserDetails->getClientId());
        $this->visitAdminPath($clientDetailsUrl);
    }

    /**
     * @When I visit the admin client details page associated with the lay deputy
     */
    public function iVisitAdminClientDetailsPageForLayDeputy()
    {
        $clientDetailsUrl = $this->getAdminClientDetailsUrl($this->layDeputySubmittedHealthWelfareDetails->getClientId());
        $this->visitAdminPath($clientDetailsUrl);
    }

    /**
     * @When I visit the admin client details page for an existing client linked to a deputy in an Organisation
     */
    public function iVisitAdminOrgClientDetailsPage()
    {
        if (!in_array($this->loggedInUserDetails->getUserRole(), $this->loggedInUserDetails::ADMIN_ROLES)) {
            throw new BehatException('Attempting to access an admin page as a non-admin user. Try logging in as an admin user instead');
        }

        $clientDetailsUrl = $this->getAdminClientDetailsUrl($this->profAdminDeputyHealthWelfareSubmittedDetails->getClientId());
        $this->visitAdminPath($clientDetailsUrl);

        $this->interactingWithUserDetails = $this->profAdminDeputyHealthWelfareSubmittedDetails;
    }

    /**
     * @When I visit the admin Add Users page
     */
    public function iVisitAdminAddUserPage()
    {
        $this->visitAdminPath($this->getAdminAddUserPage());
    }

    /**
     * @When I visit the admin Search Users page
     */
    public function iVisitAdminSearchUserPage()
    {
        $this->visitAdminPath($this->getAdminSearchUserPage());
    }

    /**
     * @When I visit the admin View User page for the user I'm interacting with
     */
    public function iVisitAdminViewUserPageForInteractingWithUser()
    {
        $this->assertInteractingWithUserIsSet();

        $this->visitAdminPath(
            $this->getAdminViewUserPage($this->interactingWithUserDetails->getUserId())
        );
    }

    /**
     * @When I visit the admin Edit User page for the user I'm interacting with
     */
    public function iVisitAdminEditUserPageForInteractingWithUser()
    {
        $this->assertInteractingWithUserIsSet();

        $this->visitAdminPath(
            $this->getAdminEditUserPage($this->interactingWithUserDetails->getUserId())
        );
    }

    /**
     * @When I visit the admin Edit User page for the admin user
     */
    public function iVisitAdminEditUserPageForTheAdminUser()
    {
        $this->visitAdminPath(
            $this->getAdminEditUserPage($this->adminDetails->getUserId())
        );
    }

    /**
     * @When I visit my admin user profile page
     */
    public function iVisitMyUserProfilePage()
    {
        $this->visitAdminPath($this->getAdminMyUserProfilePage());
    }

    /**
     * @When I visit the admin DAT report page
     */
    public function iVisitAdminDATReportPage()
    {
        $this->visitAdminPath($this->getAdminDATReportUrl());
    }

    /**
     * @When I visit the admin active lays report page
     */
    public function iVisitAdminActiveLaysPage()
    {
        $this->visitAdminPath($this->getAdminActiveLaysReportUrl());
    }

    /**
     * @When I visit the admin satisfaction report page
     */
    public function iVisitAdminSatisfactionReportPage()
    {
        $this->visitAdminPath($this->getAdminSatisfactionReportUrl());
    }

    /**
     * @When I visit the admin user research report page
     */
    public function iVisitAdminUserResearchReportPage()
    {
        $this->visitAdminPath($this->getAdminUserResearchReportUrl());
    }

    /**
     * @When I visit the admin fixtures page
     */
    public function iVisitAdminFixturesPage()
    {
        $this->visitAdminPath($this->getAdminFixturesUrl());
    }

    /**
     * @When I visit the admin tools page
     */
    public function iVisitAdminToolsPage()
    {
        $this->visitAdminPath($this->getAdminToolsUrl());
    }

    /**
     * @When I visit the admin analytics page
     */
    public function iVisitAdminAnalyticsPage()
    {
        $this->visitAdminPath($this->getAdminAnalyticsUrl());
    }

    /**
     * @When I visit the admin stats reports page
     */
    public function iVisitAdminStatsReportsPage()
    {
        $this->visitAdminPath($this->getAdminStatsReportsUrl());
    }

    /**
     * @When I visit the inactive admin users report page
     */
    public function iVisitInactiveAdminUsersReportsPage()
    {
        $this->visitAdminPath($this->getInactiveAdminusersReportUrl());
    }

    /**
     * @When I visit the admin login page
     */
    public function iVisitAdminLoginPage()
    {
        $this->visitAdminPath('/');
    }

    /**
     * @Given I visit the admin submissions page
     */
    public function iVisitAdminSubmissionsPage()
    {
        $this->visitAdminPath($this->getAdminSubmissionsPage());
    }

    /**
     * @When I visit the admin upload users page
     */
    public function iVisitAdminUploadUsersPage()
    {
        $this->visitAdminPath($this->getAdminUploadUsersUrl());
    }

    /**
     * @When I visit the admin upload org users page
     */
    public function iVisitAdminUploadOrgUsersPage()
    {
        $this->visitAdminPath($this->getAdminUploadOrgUsersUrl());
    }

    /**
     * @When I visit the admin upload lay users page
     */
    public function iVisitAdminUploadLayUsersPage()
    {
        $this->visitAdminPath($this->getAdminUploadLayUsersUrl());
    }

    /**
     * @When I visit the admin notification page
     */
    public function iVisitTheAdminNotificationPage()
    {
        $this->visitAdminPath($this->getAdminNotificationUrl());
    }

    /**
     * @When I visit the checklist page for the previously submitted report for the user I am interacting with
     */
    public function iVisitTheChecklistPageForSubmittedReport()
    {
        $this->assertInteractingWithUserIsSet();

        $this->visitAdminPath(
            $this->getAdminChecklistUrl($this->interactingWithUserDetails->getPreviousReportId())
        );
    }

    /**
     * @When I visit the checklist page for the previously submitted report for the lay deputy
     */
    public function iVisitTheChecklistPageForSubmittedReportByLayDeputy()
    {
        $this->visitAdminPath(
            $this->getAdminChecklistUrl($this->layDeputySubmittedHealthWelfareDetails->getPreviousReportId())
        );
    }

    /**
     * @When I visit the organisation Add User page for the logged-in user
     */
    public function iVisitOrganisationAddUserPageForLoggedInUser()
    {
        $this->assertLoggedInUserIsSet();
        $this->assertLoggedInUserHasOrgRole();

        $emailIdentifier = $this->loggedInUserDetails->getOrganisationEmailIdentifier();
        $organisation = $this->em->getRepository(Organisation::class)->findOneBy(['emailIdentifier' => $emailIdentifier]);

        if (is_null($organisation)) {
            throw new BehatException(sprintf('Could not find an organisation with email identifier "%s"', $emailIdentifier));
        }

        $this->visitFrontendPath($this->getOrgAddUserUrl($organisation->getId()));
    }

    public function iVisitClientDetailsUrl($clientId)
    {
        $clientDetailsUrl = $this->getAdminClientDetailsUrl($clientId);

        $this->visitAdminPath($clientDetailsUrl);
    }
}
