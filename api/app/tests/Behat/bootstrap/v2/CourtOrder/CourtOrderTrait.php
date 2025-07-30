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
use Behat\Mink\Element\NodeElement;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertStringContainsString;

trait CourtOrderTrait
{
    public CourtOrder $courtOrder;
    public array $courtOrders;
    public ClientApi $clientApi;
    private Deputy $coDeputy;
    private array $invitedDeputy = [];

    private function getDeputyForLoggedInUser(): ?Deputy
    {
        // get the deputy for the logged-in user
        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy(['email' => $this->loggedInUserDetails->getUserEmail()]);

        $deputyUid = $user->getDeputyUid();

        $deputy = $this->em
            ->getRepository(Deputy::class)
            ->findOneBy(['deputyUid' => $deputyUid]);

        return $deputy;
    }

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
    public function iAmAssociatedWithCourtOrder($numOfCourtOrders, $orderType)
    {
        $clientId = $this->loggedInUserDetails->getClientId();

        $deputy = $this->getDeputyForLoggedInUser();

        if ($numOfCourtOrders > 1) {
            $clientIds = [];

            foreach ($this->fixtureUsers as $user) {
                $clientIds[] = $user->getClientId();
            }

            $clients = [];

            foreach ($clientIds as $clientId) {
                $clients[] = $this->em
                ->getRepository(Client::class)
                ->find(['id' => $clientId]);
            }

            foreach ($clients as $client) {
                $this->courtOrders[] = $this->fixtureHelper->createAndPersistCourtOrder(
                    $orderType,
                    $client,
                    $deputy,
                    $client->getCurrentReport(),
                    $client->getNdr()
                );
            }
        }

        $client = $this->em
            ->getRepository(Client::class)
            ->find(['id' => $clientId]);

        $this->courtOrder = $this->fixtureHelper->createAndPersistCourtOrder(
            $orderType,
            $client,
            $deputy,
            $client->getCurrentReport(),
            $client->getNdr()
        );
    }

    /**
     * @Given /^I am associated with a \'([^\']*)\' court order$/
     */
    public function iAmAssociatedWithCourtOrderOfType($orderType)
    {
        $clientId = $this->loggedInUserDetails->getClientId();
        $userEmail = $this->loggedInUserDetails->getUserEmail();

        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy(['email' => $userEmail]);

        $deputy = $this->getDeputyForLoggedInUser();

        $deputy->setUser($user);
        $this->em->persist($deputy);

        $client = $this->em
            ->getRepository(Client::class)
            ->find(['id' => $clientId]);

        $this->courtOrder = $this->fixtureHelper->createAndPersistCourtOrder(
            $orderType,
            $client,
            $deputy,
            $client->getCurrentReport(),
            $client->getNdr(),
        );
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

            $courtOrderUid = $this->fixtureHelper->createAndPersistCourtOrder('pfa', $client, $deputy, $client->getCurrentReport())
                ->getCourtOrderUid();

            $this->visitFrontendPath(sprintf('/courtorder/%s', $courtOrderUid));
        }
    }

    /**
     * @When /^I visit the court order page of the \'([^\']*)\' court order that \'([^\']*)\' associated with$/
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

    /**
     * @When /^I visit the multiple court order page$/
     */
    public function iVisitTheMultipleCourtOrderPage()
    {
        $this->visitFrontendPath('/courtorder/choose-a-court-order');
    }

    /**
     * @Then /^I should see \'([^\']*)\' court orders on the page$/
     */
    public function iShouldSeeCourtOrdersOnThePage(int $arg1)
    {
        $this->iAmOnPage('{\/courtorder\/choose-a-court-order$}');

        $orders = $this->findAllXpathElements("//div[contains(concat(' ', normalize-space(@class), ' '), ' opg-overview-courtorder ')]");

        if (count($orders) !== $arg1) {
            throw new BehatException(sprintf('Expected %d orders, got %d', $arg1, count($orders)));
        }
    }

    /**
     * @Given /^I should see an NDR on the court order page with a status of \'([^\']*)\' with standard report status of \'([^\']*)\'$/
     */
    public function iShouldSeeAnNDROnTheCourtOrderPageWithAStandardReportStatusOf($arg1, $arg2)
    {
        $this->iAmOnPage(sprintf('{\/courtorder\/deputy\/%s$}', $this->courtOrder->getCourtOrderUid()));

        /** @var array<NodeElement> $paragraph */
        $paragraph = $this->findAllXpathElements('//*[@id="main-content"]/div[2]/div[1]/p');
        /** @var array<NodeElement> $ndrStatus */
        $ndrStatus = $this->findAllXpathElements('//*[@id="main-content"]/div[2]/span');
        /** @var array<NodeElement> $reportStatus */
        $reportStatus = $this->findAllXpathElements('//*[@id="main-content"]/div[2]/span');

        $text = 'Your new deputy report';
        if (!str_contains($paragraph[0]->getText(), $text)) {
            throw new BehatException(sprintf('Expected to find text \'%s\' on page, unable to find on page', $text));
        }

        if (!str_contains($ndrStatus[0]->getText(), $arg1)) {
            throw new BehatException(sprintf('Expected to find a New Deputy Report with a status of \'%s\', found a status of \'%s\' instead', $arg1, $ndrStatus[0]->getText()));
        }

        if (!str_contains($reportStatus[0]->getText(), $arg2)) {
            throw new BehatException(sprintf('Expected to find a New Deputy Report with a status of \'%s\', found a status of \'%s\' instead', $arg2, $reportStatus[0]->getText()));
        }

        $this->clickLink('Start Now');
    }

    /**
     * @Then /^I can procced to fill out the NDR$/
     */
    public function iCanProccedToFillOutTheNDR()
    {
        $this->iAmOnPage(sprintf('{\/ndr\/%s\/overview$}', $this->courtOrder->getNdr()->getId()));
    }

    /**
     * @Then /^I should see a message explaining that my account is being set up$/
     */
    public function iShouldSeeAccountBeingSetUpMessage()
    {
        $this->assertStringContainsString('Your account is being set up', $this->getPageContent(), 'page should contain message about account being set up');
    }

    /**
     * @Given /^an unregistered co-deputy is associated with the court order$/
     */
    public function anUnregisteredCoDeputyIsAssociatedWithTheCourtOrder()
    {
        // create deputy with null last_logged_in datetime, so they show as "awaiting registration"
        $this->coDeputy = $this->fixtureHelper->createDeputyOnOrder($this->courtOrder);
    }

    /**
     * @Given /^a registered co-deputy is associated with the court order$/
     */
    public function aRegisteredCoDeputyIsAssociatedWithTheCourtOrder()
    {
        // create deputy with a last_logged_in datetime, so they show as "registered",
        // and associate with the court order (mimicking what will happen when we eventually do this via ingest)
        $this->coDeputy = $this->fixtureHelper->createDeputyOnOrder($this->courtOrder, new \DateTime());
    }

    /**
     * @Given /^I should see that I am a registered deputy$/
     */
    public function iShouldSeeIAmARegisteredDeputy()
    {
        $coDeputyNameElts = $this->findAllCssElements('td[data-role="co-deputy-registered"]');
        $deputy = $this->getDeputyForLoggedInUser();

        $foundDeputy = false;
        foreach ($coDeputyNameElts as $coDeputyNameElt) {
            $eltText = $coDeputyNameElt->getText();
            if (
                str_contains($eltText, $deputy->getFirstName())
                && str_contains($eltText, $deputy->getLastName())
            ) {
                $foundDeputy = true;
            }
        }

        assert($foundDeputy);
    }

    /**
     * @Given /^I should see that the co-deputy is awaiting registration$/
     */
    public function iShouldSeeCoDeputyAwaitingRegistrationOnCourtOrder()
    {
        $coDeputyNameElts = $this->findAllCssElements('td[data-role="co-deputy-awaiting-registration"]');
        assertCount(1, $coDeputyNameElts);
        assertStringContainsString($this->coDeputy->getEmail1(), $coDeputyNameElts[0]->getText());
    }

    /**
     * @Given /^I should see that the invited co-deputy is awaiting registration$/
     */
    public function iShouldSeeInvitedCoDeputyAwaitingRegistrationOnCourtOrder()
    {
        $coDeputyNameElts = $this->findAllCssElements('td[data-role="co-deputy-awaiting-registration"]');
        assertCount(1, $coDeputyNameElts);
        assertStringContainsString($this->invitedDeputy['email'], $coDeputyNameElts[0]->getText());
    }

    /**
     * @Given /^I should see that the co-deputy is registered$/
     */
    public function iShouldSeeCoDeputyRegisteredOnCourtOrder()
    {
        $coDeputyNameElts = $this->findAllCssElements('td[data-role="co-deputy-registered"]');

        $foundDeputy = false;
        foreach ($coDeputyNameElts as $coDeputyNameElt) {
            $eltText = $coDeputyNameElt->getText();
            if (
                str_contains($eltText, $this->coDeputy->getFirstname())
                && str_contains($eltText, $this->coDeputy->getLastname())
            ) {
                $foundDeputy = true;
            }
        }

        assert($foundDeputy);
    }

    /**
     * @Given /^I invite a co-deputy to the court order$/
     */
    public function iInviteACoDeputyToTheCourtOrder(): void
    {
        // add user to be invited to the pre-reg table, associated with the case number of the court order
        $preregUser = $this->fixtureHelper->createPreRegistration(caseNumber: $this->courtOrder->getClient()->getCaseNumber());

        $this->invitedDeputy = [
            'email' => strtolower($preregUser->getDeputyFirstname()).'.'.strtolower($preregUser->getDeputySurname()).'@opg.gov.uk',
            'firstname' => $preregUser->getDeputyFirstname(),
            'lastname' => $preregUser->getDeputySurname(),
        ];

        // visit the court order invite page
        $this->visit("/courtorder/{$this->courtOrder->getCourtOrderUid()}/invite");

        // fill in invitee details and submit
        $this->fillInField('co_deputy_invite_firstname', $this->invitedDeputy['firstname']);
        $this->fillInField('co_deputy_invite_lastname', $this->invitedDeputy['lastname']);
        $this->fillInField('co_deputy_invite_email', $this->invitedDeputy['email']);
        $this->pressButton('co_deputy_invite_submit');
    }

    /**
     * @Given /^I should be on the page for the court order$/
     */
    public function iShouldBeOnCourtOrderPage(): void
    {
        $this->iAmOnPage('|/courtorder/'.$this->courtOrder->getCourtOrderUid().'$|');
    }

    /**
     * @Given /^I visit the court order invite page$/
     */
    public function iVisitTheCourtOrderInvitePage(): void
    {
        $this->visit('/courtorder/'.$this->courtOrder->getCourtOrderUid().'/invite');
    }
}
