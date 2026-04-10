<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\ACL;

use Tests\OPG\Digideps\Backend\Behat\Common\FormTrait;
use Tests\OPG\Digideps\Backend\Behat\v2\Common\BaseFeatureContext;
use Tests\OPG\Digideps\Backend\Behat\v2\CourtOrder\CourtOrderTrait;
use Tests\OPG\Digideps\Backend\Behat\v2\DeputyManagement\DeputyManagementTrait;

class ACLFeatureContext extends BaseFeatureContext
{
    use ACLTrait;
    use CourtOrderTrait;
    use DeputyManagementTrait;
    use FormTrait;

    /**
     * @When I log out
     */
    public function iLogOut(): void
    {
        $this->visitPath('/logout');
    }

    /**
     * @When I log in
     */
    public function iLogin(): void
    {
        $this->loginToFrontendAs($this->layDeputyCompletedPfaLowAssetsDetails->getUserEmail());
    }
}
