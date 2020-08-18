<?php declare(strict_types=1);

namespace DigidepsBehat\DeputyManagement;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Session;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Psr7\build_query;

trait DeputyManagementTrait
{
    /**
     * Requires a logged in user to call
     *
     * @Given I view the lay deputy edit your details page
     */
    public function viewLayEditMyDetailsPage()
    {
        $this->visit('/deputyship-details/your-details/edit');
    }

    /**
     * Requires a logged in user to call
     *
     * @Given I view the org deputy edit your details page
     */
    public function viewOrgEditMyDetailsPage()
    {
        $this->visit('/org/settings/your-details/edit');
    }

    /**
     * Requires a logged in user to call
     *
     * @Given I view the lay deputy change password page
     */
    public function viewLayChangePasswordPage()
    {
        $this->visit('/deputyship-details/your-details/change-password');
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
