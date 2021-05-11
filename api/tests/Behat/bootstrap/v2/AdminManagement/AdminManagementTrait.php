<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\AdminManagement;

use App\Entity\User;

trait AdminManagementTrait
{
    /**
     * @Then I should be able to add a super admin user
     */
    public function iShouldBeAbleToAddSuperAdmin()
    {
        $this->selectOption('admin[roleType]', 'staff');
        $this->assertValueAppearsInSelect(User::ROLE_SUPER_ADMIN, 'admin[roleNameStaff]');
    }

    /**
     * @Then I should be able to add an elevated admin user
     */
    public function iShouldBeAbleToAddElevatedAdmin()
    {
    }

    /**
     * @Then I should be able to add an admin user
     */
    public function iShouldBeAbleToAddAdmin()
    {
    }
}
