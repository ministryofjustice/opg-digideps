<?php

namespace App\Tests\Behat\v2\Common;

trait INavigateToAdminTrait
{
    /**
     * @When I navigate to the admin clients search page
     */
    public function iNavigateToAdminClientsSearchPage()
    {
        $this->clickLink('Clients');
    }

    /**
     * @When I navigate to the admin add user page
     */
    public function iNavigateToAdminAddUserPage()
    {
        $this->pressButton('Add new user');
    }
}
