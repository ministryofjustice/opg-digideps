<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\CourtOrder;

trait CourtOrderTrait
{
    /**
     * @Given I visit the court order page
     */
    public function iVisitTheCourtOrderPage()
    {
        $this->visitFrontendPath('/courtorder/700000000001');
    }
}
