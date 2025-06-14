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
use App\Tests\Behat\BehatException;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Mink\Element\NodeElement;

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
        $this->visitFrontendPath('/courtorder/deputy/700000000001');
    }

    /**
     * @Given /^I am associated with \'([^\']*)\' \'([^\']*)\' court order\(s\)$/
     */
    public function iAmAssociatedWithCourtOrder($numOfCourtOrders, $orderType)
    {
        $clientId = $this->loggedInUserDetails->getClientId();
        $userEmail = $this->loggedInUserDetails->getUserEmail();

        $deputyUid = $this->em
            ->getRepository(User::class)
            ->findOneBy(['email' => $userEmail])->getDeputyUid();

        $deputy = $this->em
            ->getRepository(Deputy::class)
            ->findOneBy(['deputyUid' => $deputyUid]);

        if ($numOfCourtOrders > 1) {
            $clientIds = [];

            $clientIds[] = $this->layPfaHighNotStartedMultiClientDeputyPrimaryUser->getClientId();
            $clientIds[] = $this->layPfaHighNotStartedMultiClientDeputyNonPrimaryUser->getClientId();

            $clients = [];

            foreach ($clientIds as $clientId) {
                $clients[] = $this->em
                ->getRepository(Client::class)
                ->find(['id' => $clientId]);
            }

            foreach ($clients as $client) {
                $this->courtOrders[] = $this->fixtureHelper->createAndPersistCourtOrder($orderType, $client, $deputy, $client->getCurrentReport());
            }
        }

        $client = $this->em
            ->getRepository(Client::class)
            ->find(['id' => $clientId]);

        $this->courtOrder = $this->fixtureHelper->createAndPersistCourtOrder($orderType, $client, $deputy, $client->getCurrentReport());
    }

    /**
     * @When /^I visit the page of a court order that \'([^\']*)\' associated with$/
     */
    public function iVisitTheCourtOrderPageThatIAmAssociatedWith($arg1)
    {
        if ('I am' === $arg1) {
            $this->visitFrontendPath(sprintf('/courtorder/deputy/%s', $this->courtOrder->getCourtOrderUid()));
        } else {
            $clientTestHelper = new ClientTestHelper();
            $deputyTestHelper = new DeputyTestHelper();

            $client = $clientTestHelper->generateClient($this->em);
            $deputy = $deputyTestHelper->generateDeputy();
            $this->em->persist($client);
            $this->em->persist($deputy);
            $this->em->flush();

            $courtOrderUid = $this->fixtureHelper->createAndPersistCourtOrder('pfa', $client, $deputy, $client->getCurrentReport())
                ->getCourtOrderUid();

            $this->visitFrontendPath(sprintf('/courtorder/deputy/%s', $courtOrderUid));
        }
    }

    /**
     * @When /^I visit the court order page of the \'([^\']*)\' court order that \'([^\']*)\' associated with$/
     */
    public function iVisitThePagesOfTheCourtOrderThatAssociatedWith($firstOrSecond, $arg)
    {
        if ('first' == $firstOrSecond && 'I am' == $arg) {
            $this->visitFrontendPath(sprintf('/courtorder/deputy/%s', $this->courtOrders[0]->getCourtOrderUid()));
        } else {
            $this->visitFrontendPath(sprintf('/courtorder/deputy/%s', $this->courtOrders[1]->getCourtOrderUid()));
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

    /**
     * @When /^I visit the multiple court order page$/
     */
    public function iVisitTheMultipleCourtOrderPage()
    {
        $this->visitFrontendPath('/courtorder/multi-report');
    }

    /**
     * @Then /^I should see \'([^\']*)\' court orders on the page$/
     */
    public function iShouldSeeCourtOrdersOnThePage(int $arg1)
    {
        $this->iAmOnPage('{\/courtorder\/multi-report$}');

        $orders = $this->findAllXpathElements("//div[contains(concat(' ', normalize-space(@class), ' '), ' opg-overview-courtorder ')]");

        if (count($orders) !== $arg1) {
            throw new BehatException(sprintf('Expected %d orders, got %d', $arg1, count($orders)));
        }
    }
}
