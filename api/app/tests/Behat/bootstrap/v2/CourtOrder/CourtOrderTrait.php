<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\CourtOrder;

use App\Entity\Client;
use App\Entity\CourtOrder;
use App\Entity\Deputy;
use App\Entity\User;
use App\Service\Client\Internal\ClientApi;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\DeputyTestHelper;

trait CourtOrderTrait
{
    public CourtOrder $courtOrder;
    public ClientApi $clientApi;

    /**
     * @Given I visit the court order page
     */
    public function iVisitTheCourtOrderPage()
    {
        $this->visitFrontendPath('/courtorder/700000000001');
    }

    /**
     * @Given /^I am associated with \'([^\']*)\' \'([^\']*)\' court order\(s\)$/
     */
    public function iAmAssociatedWithCourtOrder($num, $orderType)
    {
        $clientId = $this->loggedInUserDetails->getClientId();
        $userEmail = $this->loggedInUserDetails->getUserEmail();

        $deputyUid = $this->em
            ->getRepository(User::class)
            ->findOneBy(['email' => $userEmail])->getDeputyUid();

        $deputy = $this->em
            ->getRepository(Deputy::class)
            ->findOneBy(['deputyUid' => $deputyUid]);

        if ($num > 1) {
            $clients = $this->clientApi->getAllClientsByDeputyUid($deputyUid, 'client');

            foreach ($clients as $client) {
                $this->courtOrder = $this->fixtureHelper->createAndPersistCourtOrder($orderType, $client, $deputy);
            }
        }

        $client = $this->em
            ->getRepository(Client::class)
            ->find(['id' => $clientId]);

        $this->courtOrder = $this->fixtureHelper->createAndPersistCourtOrder($orderType, $client, $deputy);
    }

    /**
     * @When /^I visit the page of a court order that \'([^\']*)\' associated with$/
     */
    public function iVisitTheCourtOrderPageThatIAmAssociatedWith($arg1)
    {
        if ('I am' === $arg1) {
            $this->visitFrontendPath(sprintf('/courtorder/%s', $this->courtOrder->getCourtOrderUid()));
        } else {
            $clientTestHelper = new ClientTestHelper();
            $deputyTestHelper = new DeputyTestHelper();

            $client = $clientTestHelper->generateClient($this->em);
            $deputy = $deputyTestHelper->generateDeputy();
            $this->em->persist($client);
            $this->em->persist($deputy);
            $this->em->flush();

            $courtOrderUid = $this->fixtureHelper->createAndPersistCourtOrder('pfa', $client, $deputy)->getCourtOrderUid();

            $this->visitFrontendPath(sprintf('/courtorder/%s', $courtOrderUid));
        }
    }
}
