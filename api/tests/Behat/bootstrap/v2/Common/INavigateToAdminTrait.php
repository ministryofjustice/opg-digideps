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
}
