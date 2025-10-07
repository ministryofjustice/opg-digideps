<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\CourtOrder;

use App\Entity\Report\Report;
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

    private function getLoggedInUser(): ?User
    {
        return $this->em
            ->getRepository(User::class)
            ->findOneBy(['email' => $this->loggedInUserDetails->getUserEmail()]);
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
     *
     * Associate the logged in user with the specified number of court orders.
     * This will create new clients and court orders if necessary, otherwise reuses existing one.
     * If the existing client has an NDR, then the court order may be linked with that NDR.
     */
    public function iAmAssociatedWithCourtOrder(int $numOfCourtOrders, string $orderType, bool $associateNdr = true): void
    {
        $user = $this->getLoggedInUser();

        // if user's deputy doesn't exist, create it: we need this to associate them with court orders
        $deputy = $this->em->getRepository(Deputy::class)->findOneBy(['deputyUid' => $user->getDeputyUid()]);
        if (is_null($deputy)) {
            $deputy = new Deputy();
            $deputy->setDeputyUid("{$user->getDeputyUid()}");
            $deputy->setFirstname($user->getFirstname());
            $deputy->setLastname($user->getLastname());
            $deputy->setEmail1($user->getEmail());
        }

        $deputy->setUser($user);
        $user->setDeputy($deputy);

        $this->em->persist($deputy);
        $this->em->persist($user);
        $this->em->flush();

        for ($i = 0; $i < $numOfCourtOrders; $i++) {
            $ndr = null;

            if (0 === $i) {
                // use the user's existing client
                $client = $this->em->getRepository(Client::class)->find(['id' => $this->loggedInUserDetails->getClientId()]);
                $report = $client->getCurrentReport();
                if ($associateNdr) {
                    $ndr = $client->getNdr();
                }
            } else {
                // create a new client
                $client = $this->fixtureHelper->generateClient($user);
                $this->em->persist($client);

                // create a new report
                $type = Report::TYPE_HEALTH_WELFARE;
                if ('pfa' === $orderType) {
                    $type = Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS;
                }

                $now = new \DateTime();

                $report = new Report($client, $type, $now, $now, false);
                $this->em->persist($report);
            }

            $this->courtOrders[] = $this->fixtureHelper->createAndPersistCourtOrder(
                $orderType,
                $client,
                $deputy,
                $report,
                $ndr
            );
        }

        $this->courtOrder = $this->courtOrders[0];
    }

    /**
     * @Given /^I am associated with \'([^\']*)\' \'([^\']*)\' court order\(s\) but not their NDRs$/
     */
    public function iAmAssociatedWithCourtOrderButNotNdr(int $numOfCourtOrders, string $orderType): void
    {
        $this->iAmAssociatedWithCourtOrder($numOfCourtOrders, $orderType, false);
    }

    /**
     * @Given all the reports for the first client are associated with a :orderType court order
     */
    public function allTheReportsForFirstClientOnCourtOrder(string $orderType): void
    {
        // get the client
        $client = $this->em->getRepository(Client::class)->find(['id' => $this->loggedInUserDetails->getClientId()]);

        // get the deputy
        $user = $this->getLoggedInUser();
        $deputy = $this->em->getRepository(Deputy::class)->findOneBy(['deputyUid' => $user->getDeputyUid()]);

        // create a court order
        $this->courtOrder = $this->fixtureHelper->createAndPersistCourtOrder(
            $orderType,
            $client,
            $deputy,
        );

        // associate all the reports with that court order
        foreach ($client->getReports() as $report) {
            $this->courtOrder->addReport($report);
        }

        $this->em->persist($this->courtOrder);
        $this->em->flush();
    }

    /**
     * @Given /^I am associated with a \'([^\']*)\' court order$/
     */
    public function iAmAssociatedWithCourtOrderOfType($orderType)
    {
        $clientId = $this->loggedInUserDetails->getClientId();

        $client = $this->em
            ->getRepository(Client::class)
            ->find(['id' => $clientId]);

        $user = $this->getLoggedInUser();

        $this->courtOrder = $this->fixtureHelper->createAndPersistCourtOrder(
            $orderType,
            $client,
            $user->getDeputy(),
            $client->getCurrentReport(),
            $client->getNdr(),
        );

        // associate all of the client's reports with the court order
        foreach ($client->getReports() as $report) {
            $this->courtOrder->addReport($report);
        }

        $this->em->persist($this->courtOrder);
        $this->em->flush();
    }

    /**
     * @When /^I visit the page of a court order that \'([^\']*)\' associated with$/
     */
    public function iVisitTheCourtOrderPageThatIAmAssociatedWith($arg1)
    {
        if ('I am' === $arg1) {
            $this->visitFrontendPath(sprintf('/courtorder/%s', $this->courtOrder->getCourtOrderUid()));
        } else {
            $clientTestHelper = ClientTestHelper::create();
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
        /** @var array<NodeElement> $ndrHeading */
        $ndrHeading = $this->findAllCssElements('main h2');

        /** @var array<NodeElement> $ndrStatus */
        $ndrStatus = $this->findAllXpathElements('//div[contains(@class, "behat-region-ndr-card")]/span[contains(@class, "opg-card__tag")]');

        /** @var array<NodeElement> $reportStatus */
        $reportStatus = $this->findAllXpathElements('//div[contains(@class, "behat-region-report-card")]/span[contains(@class, "opg-card__tag")]');

        // second h2 inside main is the new deputy report; TODO make this not so brittle
        $text = 'New deputy report';
        if (!str_contains($ndrHeading[1]->getText(), $text)) {
            throw new BehatException(sprintf('Expected to find text \'%s\' on page, unable to find on page', $text));
        }

        if (!str_contains($ndrStatus[0]->getText(), $arg1)) {
            throw new BehatException(sprintf('Expected to find a New Deputy Report with a status of \'%s\', found a status of \'%s\' instead', $arg1, $ndrStatus[0]->getText()));
        }

        if (!str_contains($reportStatus[0]->getText(), $arg2)) {
            throw new BehatException(sprintf('Expected to find a New Deputy Report with a status of \'%s\', found a status of \'%s\' instead', $arg2, $reportStatus[0]->getText()));
        }

        $this->clickLink('Start now');
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
        $deputy = $this->getLoggedInUser()->getDeputy();

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
            'email' => strtolower($preregUser->getDeputyFirstname()) . '.' . strtolower($preregUser->getDeputySurname()) . '@opg.gov.uk',
            'firstname' => $preregUser->getDeputyFirstname(),
            'lastname' => $preregUser->getDeputySurname(),
        ];

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
        $this->iAmOnPage('|/courtorder/' . $this->courtOrder->getCourtOrderUid() . '$|');
    }

    /**
     * @Given /^I visit the court order invite page$/
     */
    public function iVisitTheCourtOrderInvitePage(): void
    {
        $this->visit('/courtorder/' . $this->courtOrder->getCourtOrderUid() . '/invite');
    }

    /**
     * @Given /^the latest unsubmitted report for the court order is a Pfa High Assets report$/
     */
    public function latestUnsubmittedCourtOrderReportIsPfaHigh(): void
    {
        $this->fixtureHelper->setCourtOrderLatestReportType($this->courtOrder, Report::LAY_PFA_LOW_ASSETS_TYPE);
    }
}
