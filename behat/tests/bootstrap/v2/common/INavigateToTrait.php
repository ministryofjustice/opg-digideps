<?php declare(strict_types=1);


namespace DigidepsBehat\v2\Common;

trait INavigateToTrait
{
    /**
     * @When I navigate to the admin clients search page
     */
    public function navigateToAdminClients()
    {
        $this->visitAdminPath('/admin');
        $this->clickLink('Clients');
    }
}
