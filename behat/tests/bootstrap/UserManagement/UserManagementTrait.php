<?php declare(strict_types=1);

namespace DigidepsBehat\UserManagement;

use Behat\Mink\Session;

trait UserManagementTrait
{
    /**
     * Requires a logged in admin user to call
     *
     * @Given I am viewing the edit user page for :userEmail
     */
    public function viewEditUserPageFor($userEmail)
    {
        $this->visitAdminPath("/admin/fixtures/getUserIDByEmail/" . strtolower($userEmail));

        /** @var Session $session */
        $session = $this->getSession();

        if ($session->getStatusCode() !== Response::HTTP_OK) {
            throw new \Exception($session->getPage()->getContent());
        }

        $id = $session->getPage()->getContent();
        $this->visitAdminPath("/admin/edit-user?filter=$id");
    }

    /**
     * Requires a logged in admin user to call
     *
     * @Then the user :userEmail should be deleted
     */
    public function userShouldBeDeleted($userEmail)
    {
        $this->visitAdminPath("/admin/fixtures/getUserIDByEmail/" . strtolower($userEmail));

        /** @var Session $session */
        $session = $this->getSession();

        if ($session->getStatusCode() === Response::HTTP_OK) {
            throw new \Exception("The user '$userEmail' should have been deleted but they still exist");
        }

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }
}
