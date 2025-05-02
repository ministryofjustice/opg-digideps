<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\CourtOrder;

use App\Entity\Client;
use App\Entity\CourtOrder;
use App\Entity\Deputy;
use App\Entity\User;

trait CourtOrderTrait
{
    public CourtOrder $courtOrder;

    /**
     * @Given I visit the court order page
     */
    public function iVisitTheCourtOrderPage()
    {
        $this->visitFrontendPath('/courtorder/700000000001');
    }

    /**
     * @Given /^I am associated with one \'([^\']*)\' court order$/
     */
    public function iAmAssociatedWithOneCourtOrder($orderType)
    {
        $clientId = $this->loggedInUserDetails->getClientId();
        $reportStartDate = $this->loggedInUserDetails->getCurrentReportStartDate();
        $userEmail = $this->loggedInUserDetails->getUserEmail();

        $client = $this->em
            ->getRepository(Client::class)
            ->find(['id' => $clientId]);

        $deputyUid = $this->em
            ->getRepository(User::class)
            ->findOneBy(['email' => $userEmail])->getDeputyUid();

        $deputy = $this->em
            ->getRepository(Deputy::class)
            ->findOneBy(['deputyUid' => $deputyUid]);

        $this->courtOrder = $this->fixtureHelper->createAndPersistCourtOrder($orderType, $client, $reportStartDate, $deputy);
    }

    /**
     * @When /^I visit the page of the court order that \'([^\']*)\' associated with$/
     */
    public function iVisitTheCourtOrderPageThatIAmAssociatedWith($arg1)
    {
        if ('I am' === $arg1) {
            $this->visitFrontendPath(sprintf('/courtorder/%s', $this->courtOrder->getCourtOrderUid()));
        } else {
            $this->visitFrontendPath('/courtorder/700000000001');
        }
    }
}
