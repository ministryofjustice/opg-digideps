<?php

namespace DigidepsBehat\UserManagement;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Session;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Psr7\build_query;

trait UserManagementTrait
{
    /**
     * it's assumed you are logged as an admin and you are on the admin homepage (with add user form).
     *
     * @Given the following clients exist and are attached to deputies:
     */
    public function clientsExist(TableNode $table)
    {
        foreach ($table as $inputs) {
            $query = build_query($inputs);
            $this->visitAdminPath("/admin/fixtures/createClientAttachDeputy?$query");
        }
    }

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
}
