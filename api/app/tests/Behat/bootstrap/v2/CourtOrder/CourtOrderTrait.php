<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\CourtOrder;

use App\Entity\Client;
use App\Entity\CourtOrder;
use App\Entity\CourtOrderDeputy;
use App\Entity\Deputy;
use App\Entity\User;
use App\Service\Client\Internal\ClientApi;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\DeputyTestHelper;

trait CourtOrderTrait
{
    public CourtOrder $courtOrder;
    public array $courtOrders;
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
            $clients = [];
            $primaryClientId = $this->layPfaHighNotStartedMultiClientDeputyPrimaryUser->getClientId();
            $secondaryClientId = $this->layPfaHighNotStartedMultiClientDeputyNonPrimaryUser->getClientId();

            $clients[] = $this->em
                ->getRepository(Client::class)
                ->find(['id' => $primaryClientId]);

            $clients[] = $this->em
                ->getRepository(Client::class)
                ->find(['id' => $secondaryClientId]);

            foreach ($clients as $client) {
                $this->courtOrders[] = $this->fixtureHelper->createAndPersistCourtOrder($orderType, $client, $deputy);
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

    /**
     * @When /^I visit the pages of the \'([^\']*)\' court order that \'([^\']*)\' associated with$/
     */
    public function iVisitThePagesOfTheCourtOrderThatAssociatedWith($firstOrSecond, $arg)
    {
        if ('first' == $firstOrSecond && 'I am' == $arg) {
            $this->visitFrontendPath(sprintf('/courtorder/%s', $this->courtOrders[0]->getCourtOrderUid()));
        } else {
            $this->visitFrontendPath(sprintf('/courtorder/%s', $this->courtOrders[1]->getCourtOrderUid()));
        }
    }

    /**
     * @When /^I am discharged from the court order$/
     */
    public function iAmDischargedFromTheCourtOrder()
    {
        $courtOrderDeputy = $this->em
            ->getRepository(CourtOrderDeputy::class)
            ->findOneBy(['courtOrder' => $this->courtOrder->getId()]);

        $courtOrderDeputy->setIsActive(false);

        $this->em->persist($courtOrderDeputy);
        $this->em->flush();
    }
}
