<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\AdminManagement;

use App\Entity\User;

trait AdminManagementTrait
{
    private array $completedFormFields = [];

    /**
     * @Then I should be able to add a super admin user
     */
    public function iShouldBeAbleToAddSuperAdmin()
    {
        $this->selectOption('admin[roleType]', 'staff');
        $this->assertValueIsInSelect(User::ROLE_SUPER_ADMIN, 'admin[roleNameStaff]');
    }

    /**
     * @Then I should be able to add an elevated admin user
     */
    public function iShouldBeAbleToAddElevatedAdmin()
    {
        $this->selectOption('admin[roleType]', 'staff');
        $this->assertValueIsInSelect(User::ROLE_ELEVATED_ADMIN, 'admin[roleNameStaff]');
    }

    /**
     * @Then I should be able to add an admin user
     */
    public function iShouldBeAbleToAddAdmin()
    {
        $this->selectOption('admin[roleType]', 'staff');
        $this->assertValueIsInSelect(User::ROLE_ADMIN, 'admin[roleNameStaff]');
    }

    /**
     * @Then I should not be able to add a super admin user
     */
    public function iShouldNotBeAbleToAddSuperAdmin()
    {
        $this->selectOption('admin[roleType]', 'staff');
        $this->assertValueIsNotInSelect(User::ROLE_SUPER_ADMIN, 'admin[roleNameStaff]');
    }

    /**
     * @Then I should not be able to add an elevated admin user
     */
    public function iShouldNotBeAbleToAddElevatedAdmin()
    {
        $this->selectOption('admin[roleType]', 'staff');
        $this->assertValueIsNotInSelect(User::ROLE_ELEVATED_ADMIN, 'admin[roleNameStaff]');
    }

    /**
     * @When I enter valid details for a new super admin user
     */
    public function iAddANewSuperAdminUser()
    {
        $this->iVisitAdminAddUserPage();
        $this->setNewUserFormValues(User::ROLE_SUPER_ADMIN);
        $this->selectOption('admin[roleType]', 'staff');

        foreach ($this->completedFormFields['text'] as $fieldName => $fieldValue) {
            $this->fillField($fieldName, $fieldValue);
        }

        foreach ($this->completedFormFields['select'] as $fieldName => $fieldValue) {
            $this->fillField($fieldName, $fieldValue);
        }
    }

    /**
     * @When I enter valid details for a new elevated admin user
     */
    public function iAddANewElevatedAdminUser()
    {
        $this->iVisitAdminAddUserPage();
        $this->setNewUserFormValues(User::ROLE_ELEVATED_ADMIN);
        $this->selectOption('admin[roleType]', 'staff');

        foreach ($this->completedFormFields['text'] as $fieldName => $fieldValue) {
            $this->fillField($fieldName, $fieldValue);
        }

        foreach ($this->completedFormFields['select'] as $fieldName => $fieldValue) {
            $this->fillField($fieldName, $fieldValue);
        }
    }

    /**
     * @When I enter valid details for a new admin user
     */
    public function iAddANewAdminUser()
    {
        $this->iVisitAdminAddUserPage();
        $this->setNewUserFormValues(User::ROLE_ADMIN);
        $this->selectOption('admin[roleType]', 'staff');

        foreach ($this->completedFormFields['text'] as $fieldName => $fieldValue) {
            $this->fillField($fieldName, $fieldValue);
        }

        foreach ($this->completedFormFields['select'] as $fieldName => $fieldValue) {
            $this->fillField($fieldName, $fieldValue);
        }
    }

    private function setNewUserFormValues(string $roleName)
    {
        $this->completedFormFields['text']['admin[email]'] = $this->faker->safeEmail;
        $this->completedFormFields['text']['admin[firstname]'] = $this->faker->firstName;
        $this->completedFormFields['text']['admin[lastname]'] = $this->faker->lastName;
        $this->completedFormFields['text']['admin[addressPostcode]'] = $this->faker->postcode;

        if (in_array($roleName, User::$adminRoles)) {
            $this->completedFormFields['select']['admin[roleNameStaff]'] = $roleName;
        }
    }

    /**
     * @When I submit the form
     */
    public function iSubmitTheForm()
    {
        $this->pressButton('Save user');
    }

    /**
     * @Then the new user should be added
     */
    public function theNewUserShouldBeAdded()
    {
        $this->iAmOnAdminUsersSearchPage();
        $this->assertOnAlertMessage('email has been sent to the user');

        $addedUserEmail = $this->completedFormFields['text']['admin[email]'];
        $addedUser = $this->em
            ->getRepository(User::class)
            ->findOneBy(
                ['email' => $addedUserEmail]
            );

        $this->assertIsClass(
            User::class,
            $addedUser,
            sprintf('User retrieved from database with email \'%s\'', $addedUserEmail)
        );
    }

    /**
     * @When I attempt to delete an existing :role user
     */
    public function iAttemptToDeleteExistingAdminUser($role)
    {
        if (is_null($this->interactingWithUserDetails)) {
            switch (strtolower($role)) {
                case 'super admin':
                    $this->interactingWithUserDetails = $this->superAdminDetails;
                    break;
                case 'elevated admin':
                    $this->interactingWithUserDetails = $this->elevatedAdminDetails;
                    break;
                case 'admin':
                    $this->interactingWithUserDetails = $this->adminDetails;
                    break;
                default:
                    $this->throwContextualException('Admin role not recognised');
                    break;
            }
        }

        $this->iVisitAdminEditUserPageForInteractingWithUser();

        try {
            $this->assertLinkWithTextIsOnPage('Delete user');
            $this->clickLink('Delete user');
            $this->clickLink("Yes, I'm sure");
        } catch (\Throwable $e) {
            // Swallow error as we want to assert on deleting user in further step
        }
    }

    /**
     * @Then the user should be deleted
     */
    public function theUserShouldBeDeleted()
    {
        $email = $this->interactingWithUserDetails->getUserEmail();
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        $this->assertIsNull($user, sprintf('Queried DB for User with email %s', $email));
        $this->interactingWithUserDetails = null;
    }

    /**
     * @Then the user should not be deleted
     */
    public function theUserShouldNotBeDeleted()
    {
        $email = $this->interactingWithUserDetails->getUserEmail();
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        $this->assertIsClass(User::class, $user, sprintf('Queried DB for User with email %s', $email));
        $this->interactingWithUserDetails = null;
    }
}
