<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\AdminManagement;

use App\Entity\User;
use App\Tests\Behat\BehatException;

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
     * @Then I should be able to add an admin manager user
     */
    public function iShouldBeAbleToAddAdminManager()
    {
        $this->selectOption('admin[roleType]', 'staff');
        $this->assertValueIsInSelect(User::ROLE_ADMIN_MANAGER, 'admin[roleNameStaff]');
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
     * @Then I should not be able to add an admin manager user
     */
    public function iShouldNotBeAbleToAddAdminManager()
    {
        $this->selectOption('admin[roleType]', 'staff');
        $this->assertValueIsNotInSelect(User::ROLE_ADMIN_MANAGER, 'admin[roleNameStaff]');
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
     * @When I enter valid details for a new admin manager user
     */
    public function iAddANewAdminManagerUser()
    {
        $this->iVisitAdminAddUserPage();
        $this->setNewUserFormValues(User::ROLE_ADMIN_MANAGER);
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
        $this->completedFormFields['text']['admin[email]'] = $this->faker->safeEmail();
        $this->completedFormFields['text']['admin[firstname]'] = $this->faker->firstName();
        $this->completedFormFields['text']['admin[lastname]'] = $this->faker->lastName();
        $this->completedFormFields['text']['admin[addressPostcode]'] = $this->faker->postcode();

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
                case 'admin manager':
                    $this->interactingWithUserDetails = $this->adminManagerDetails;
                    break;
                case 'admin':
                    $this->interactingWithUserDetails = $this->adminDetails;
                    break;
                default:
                    throw new BehatException('Admin role not recognised');
            }
        }

        if (User::ROLE_ADMIN_MANAGER === $this->loggedInUserDetails->getUserRole() && 'admin manager' === strtolower($role)) {
            $this->iVisitAdminViewUserPageForInteractingWithUser();
        } else {
            $this->iVisitAdminEditUserPageForInteractingWithUser();
        }

        try {
            $this->assertLinkWithTextIsOnPage('Delete user');
            $this->clickLink('Delete user');
            $this->clickLink("Yes, I'm sure");
        } catch (\Throwable $e) {
//             Swallow error as we want to assert on deleting user in further step
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

    /**
     * @When I update my firstname and lastname
     */
    public function updateMyFirstnameAndLastname()
    {
        $this->clickLink('Edit your details');
        $this->completedFormFields['user_details[firstname]'] = $this->faker->firstName();
        $this->completedFormFields['user_details[lastname]'] = $this->faker->lastName();

        foreach ($this->completedFormFields as $fieldName => $fieldValue) {
            $this->fillField($fieldName, $fieldValue);
        }

        $this->pressButton('Save');
    }

    /**
     * @Then my details should be updated
     */
    public function myDetailsShouldBeUpdated()
    {
        $locator = "//th[text()='Full name']/parent::tr";
        $fullnameTableRow = $this->getSession()->getPage()->find('xpath', $locator);
        $foundName = $fullnameTableRow->find('xpath', '//td')->getHtml();

        $expectedName = sprintf(
            '%s %s',
            $this->completedFormFields['user_details[firstname]'],
            $this->completedFormFields['user_details[lastname]'],
        );

        $this->assertStringEqualsString($expectedName, $foundName, 'Full name profile td element');
    }

    /**
     * @When I attempt to update an existing :role users details
     */
    public function iAttemptToUpdateExistingAdminUser($role)
    {
        if (is_null($this->interactingWithUserDetails)) {
            switch (strtolower($role)) {
                case 'super admin':
                    $this->interactingWithUserDetails = $this->superAdminDetails;
                    break;
                case 'admin manager':
                    $this->interactingWithUserDetails = $this->adminManagerDetails;
                    break;
                case 'admin':
                    $this->interactingWithUserDetails = $this->adminDetails;
                    break;
                default:
                    throw new BehatException('Admin role not recognised');
            }
        }

        $this->iVisitAdminEditUserPageForInteractingWithUser();

        try {
            $this->completedFormFields['admin[firstname]'] = $this->faker->firstName();
            $this->completedFormFields['admin[lastname]'] = $this->faker->lastName();
            $this->completedFormFields['admin[addressPostcode]'] = $this->faker->postcode();

            if (User::ROLE_SUPER_ADMIN === $this->loggedInUserDetails->getUserRole()) {
                $this->completedFormFields['admin[email]'] = $this->faker->safeEmail();
            }

            foreach ($this->completedFormFields as $fieldName => $fieldValue) {
                $this->fillField($fieldName, $fieldValue);
            }

            $this->pressButton('Update user');
        } catch (\Throwable $e) {
            // Swallow error as we want to assert on deleting user in further step
        }

        $this->em->clear();
    }

    /**
     * @Then the users details should be updated
     */
    public function theUserShouldBeUpdated()
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

        if (User::ROLE_SUPER_ADMIN === $this->loggedInUserDetails->getUserRole()) {
            $this->assertStringEqualsString(
                $this->completedFormFields['admin[email]'],
                $user->getEmail(),
                $comparisonSubjectMessage
            );
        }

        $this->interactingWithUserDetails = null;
        $this->completedFormFields = [];
    }

    /**
     * @Then the users details should not be updated
     */
    public function theUserShouldNotBeUpdated()
    {
        $email = $this->interactingWithUserDetails->getUserEmail();
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

        $comparisonSubjectMessage = sprintf('Queried DB for User with email %s against interactingWithUserDetails', $email);
        $this->assertStringEqualsString(
            $this->interactingWithUserDetails->getUserFirstName(),
            $user->getFirstname(),
            $comparisonSubjectMessage
        );
        $this->assertStringEqualsString(
            $this->interactingWithUserDetails->getUserLastName(),
            $user->getLastname(),
            $comparisonSubjectMessage
        );
        $this->assertStringEqualsString(
            $this->interactingWithUserDetails->getUserFullAddressArray()['addressPostcode'],
            $user->getAddressPostcode(),
            $comparisonSubjectMessage
        );

        if (User::ROLE_SUPER_ADMIN === $this->loggedInUserDetails->getUserRole()) {
            $this->assertStringEqualsString(
                $this->interactingWithUserDetails->getUserEmail(),
                $user->getEmail(),
                $comparisonSubjectMessage
            );
        }

        $this->interactingWithUserDetails = null;
        $this->completedFormFields = [];
    }

    /**
     * @When I delete the existing admin user
     */
    public function IDeletedTheExistingAdminUser()
    {
        $this->iVisitAdminEditUserPageForTheAdminUser();

        $this->assertLinkWithTextIsOnPage('Delete user');
        $this->clickLink('Delete user');
        $this->clickLink("Yes, I'm sure");

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $this->adminDetails->getUserEmail()]);
        $this->assertIsNull($user, sprintf('Queried DB for User with email %s', $this->adminDetails->getUserEmail()));
    }
}
