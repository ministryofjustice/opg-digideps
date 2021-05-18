<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\DeputyManagement;

use App\Entity\User;
use Behat\Mink\Session;
use Symfony\Component\HttpFoundation\Response;

trait DeputyManagementTrait
{
    private array $completedFormFields = [];

    /**
     * Requires a logged in user to call.
     *
     * @Given I view the lay deputy edit your details page
     */
    public function viewLayEditMyDetailsPage()
    {
        $this->visit('/deputyship-details/your-details/edit');
    }

    /**
     * Requires a logged in user to call.
     *
     * @Given I view the org deputy edit your details page
     */
    public function viewOrgEditMyDetailsPage()
    {
        $this->visit('/org/settings/your-details/edit');
    }

    /**
     * Requires a logged in user to call.
     *
     * @Given I view the lay deputy change password page
     */
    public function viewLayChangePasswordPage()
    {
        $this->visit('/deputyship-details/your-details/change-password');
    }

    /**
     * Requires a logged in admin user to call.
     *
     * @Then the user :userEmail should be deleted
     */
    public function userShouldBeDeleted($userEmail)
    {
        $this->visitAdminPath('/admin/fixtures/getUserIDByEmail/'.strtolower($userEmail));

        /** @var Session $session */
        $session = $this->getSession();

        if (Response::HTTP_OK === $session->getStatusCode()) {
            throw new \Exception("The user '$userEmail' should have been deleted but they still exist");
        }

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * @Then I should be able to update the deputies firstname, lastname, postcode and email address
     */
    public function iShouldBeAbleToUpdateFirstnameLastnamePostcodeEmail()
    {
        $this->iShouldBeAbleToUpdateFirstnameLastnamePostcode();

        $this->assertElementOnPage('input[name="admin[email]"]');

        $this->completedFormFields['admin[email]'] = $this->faker->safeEmail;
    }

    /**
     * @Then I should be able to update the deputies firstname, lastname and postcode
     */
    public function iShouldBeAbleToUpdateFirstnameLastnamePostcode()
    {
        $this->iVisitAdminEditUserPageForInteractingWithUser();

        $this->assertElementOnPage('input[name="admin[firstname]"]');
        $this->assertElementOnPage('input[name="admin[lastname]"]');
        $this->assertElementOnPage('input[name="admin[addressPostcode]"]');

        $this->completedFormFields['admin[firstname]'] = $this->faker->firstname;
        $this->completedFormFields['admin[lastname]'] = $this->faker->lastname;
        $this->completedFormFields['admin[addressPostcode]'] = $this->faker->postcode;
    }

    /**
     * @Then I should not be able to update the deputies email address
     */
    public function iShouldNotBeAbleToUpdateEmail()
    {
        $this->iVisitAdminEditUserPageForInteractingWithUser();
        $this->assertElementNotOnPage('input[name="admin[email]"]');
    }

    /**
     * @When I update the details of the deputy available to me
     */
    public function iUpdateTheUsersDetailsAvailableToMe()
    {
        foreach ($this->completedFormFields as $fieldName => $fieldValue) {
            $this->fillField($fieldName, $fieldValue);
        }

        $this->pressButton('Update user');
        $this->em->clear();
    }

    /**
     * @Then the deputies details should be updated
     */
    public function theDeputyShouldBeUpdated()
    {
        $this->assertOnAlertMessage('Your changes were saved');
        $id = $this->interactingWithUserDetails->getUserId();
        $user = $this->em->getRepository(User::class)->find($id);

        $comparisonSubjectMessage = sprintf('Queried DB for User with id %s against form values entered', $id);
        $this->assertStringEqualsString(
            $this->completedFormFields['admin[firstname]'],
            $user->getFirstname(),
            $comparisonSubjectMessage
        );
        $this->assertStringEqualsString(
            $this->completedFormFields['admin[lastname]'],
            $user->getLastname(),
            $comparisonSubjectMessage
        );
        $this->assertStringEqualsString(
            $this->completedFormFields['admin[addressPostcode]'],
            $user->getAddressPostcode(),
            $comparisonSubjectMessage
        );

        if ($this->emailShouldBeUpdated($this->loggedInUserDetails->getUserRole(), $user->getRoleName())) {
            $this->assertStringEqualsString(
                $this->completedFormFields['admin[email]'],
                $user->getEmail(),
                $comparisonSubjectMessage
            );
        }

        $this->interactingWithUserDetails = null;
        $this->completedFormFields = [];
    }

    private function emailShouldBeUpdated(string $loggedInUserRole, string $userToBeUpdatedRole): bool
    {
        $rolesAdminManagersCanUpdateEmail = [
            User::ROLE_LAY_DEPUTY,
            USER::ROLE_PROF,
            USER::ROLE_PROF_ADMIN,
            USER::ROLE_PROF_NAMED,
            USER::ROLE_PROF_TEAM_MEMBER,
            USER::ROLE_PA,
            USER::ROLE_PA_ADMIN,
            USER::ROLE_PA_NAMED,
            USER::ROLE_PA_TEAM_MEMBER,
        ];

        $isSuperAdmin = User::ROLE_SUPER_ADMIN === $loggedInUserRole;

        $adminManagerIsEditingNonAdminUser =
            (User::ROLE_ADMIN_MANAGER === $loggedInUserRole &&
                in_array($userToBeUpdatedRole, $rolesAdminManagersCanUpdateEmail));

        return $isSuperAdmin || $adminManagerIsEditingNonAdminUser;
    }
}
