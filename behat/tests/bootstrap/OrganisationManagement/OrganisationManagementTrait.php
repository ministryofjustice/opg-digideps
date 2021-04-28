<?php

namespace DigidepsBehat\OrganisationManagement;

use Behat\Gherkin\Node\TableNode;

trait OrganisationManagementTrait
{
    /**
     * @Given the following organisations exist:
     */
    public function organisationsExist(TableNode $table)
    {
        $this->iAmLoggedInToAdminAsWithPassword('admin@publicguardian.gov.uk', 'DigidepsPass1234');

        foreach ($table as $inputs) {
            $this->visitAdminPath('/admin/organisations/add');
            $this->fillField('organisation_name', $inputs['name']);

            if (substr($inputs['emailIdentifier'], 0, 1) === '@') {
                $this->fillField('organisation_emailIdentifierType_0', 'domain');
                $this->fillField('organisation_emailDomain', substr($inputs['emailIdentifier'], 1));
            } else {
                $this->fillField('organisation_emailIdentifierType_1', 'address');
                $this->fillField('organisation_emailAddress', $inputs['emailIdentifier']);
            }

            if ($inputs['activated']) {
                $this->fillField('organisation_isActivated_0', '1');
            }

            $this->pressButton('Save organisation');
        }
    }

    /**
     * @Given the following users are in the organisations:
     */
    public function usersAreInOrgs(TableNode $table)
    {
        $this->iAmLoggedInToAdminAsWithPassword('admin@publicguardian.gov.uk', 'DigidepsPass1234');

        foreach ($table as $inputs) {
            $this->visitAdminPath('/admin/organisations');
            $this->clickLink($inputs['orgName']);
            $this->clickLink('Add user');
            $this->fillField('organisation_add_user_email', $inputs['userEmail']);
            $this->pressButton('Find user');
            $this->pressButton('Add user to organisation');

            if ($this->getSession()->getStatusCode() > 399) {
                throw new \Exception($this->getSession()->getPage()->getContent());
            }
        }
    }

    /**
     * @Given the following users clients are in the users organisation:
     */
    public function usersClientsAreInUsersOrg(TableNode $table)
    {
        $this->iAmLoggedInToAdminAsWithPassword('admin@publicguardian.gov.uk', 'DigidepsPass1234');

        foreach ($table as $inputs) {
            $this->visitAdminPath(sprintf("/admin/fixtures/move-users-clients-to-users-org/%s", $inputs['userEmail']));

            if ($this->getSession()->getStatusCode() > 399) {
                throw new \Exception($this->getSession()->getPage()->getContent());
            }
        }
    }

    /**
     * @Given the :orgName organisation is activated
     */
    public function theOrgIsActivated(string $orgName)
    {
        $this->iAmLoggedInToAdminAsWithPassword('admin@publicguardian.gov.uk', 'DigidepsPass1234');

        $this->visitAdminPath(sprintf("/admin/fixtures/activateOrg/%s", $orgName));

        if ($this->getSession()->getStatusCode() > 399) {
            throw new \Exception($this->getSession()->getPage()->getContent());
        }
    }
}
