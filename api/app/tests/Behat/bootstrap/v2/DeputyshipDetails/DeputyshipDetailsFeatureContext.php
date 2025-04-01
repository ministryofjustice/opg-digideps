<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\DeputyshipDetails;

use App\Entity\User;
use App\Tests\Behat\v2\Common\BaseFeatureContext;
use Faker\Core\Uuid;

class DeputyshipDetailsFeatureContext extends BaseFeatureContext
{
    private User $user;
    private const FIRST_NAMES = ['Zardome', 'Ablent', 'Giga'];

    /**
     * @AfterScenario @deputyship-details-client-list
     */
    public function cleanup(): void
    {
        $this->em->remove($this->user);
        $this->em->flush();
    }

    /**
     * @Given a lay deputy with multiple clients exists
     */
    public function aLayDeputyWithMultipleClientsExists(): void
    {
        $email = (new Uuid())->uuid3().'@opg-testing.gov.uk';
        $this->user = $this->fixtureHelper->createAndPersistUser(
            roleName: User::ROLE_LAY_DEPUTY,
            email: $email,
            firstName: 'Maabaganttt',
            lastName: 'Tefooliant',
        );

        $client1 = $this->fixtureHelper->generateClient($this->user);
        $client1->setFirstname(self::FIRST_NAMES[0]);

        $client2 = $this->fixtureHelper->generateClient($this->user);
        $client2->setFirstname(self::FIRST_NAMES[1]);

        $client3 = $this->fixtureHelper->generateClient($this->user);
        $client3->setFirstname(self::FIRST_NAMES[2]);

        $this->user->addClient($client1);
        $this->user->addClient($client2);
        $this->user->addClient($client3);

        $this->em->flush();
        $this->em->persist($this->user);
    }

    /**
     * @When that lay deputy logs in
     */
    public function layDeputyLogsIn(): void
    {
        $this->visitPath('/login');
        $this->fillField('login_email', $this->user->getEmail());
        $this->fillField('login_password', 'DigidepsPass1234');
        $this->pressButton('login_login');
    }

    /**
     * @When they navigate to the client list page
     */
    public function navigateToClientsListPage(): void
    {
        $this->visitPath('/deputyship-details/clients');
    }

    /**
     * @Then they should see their clients listed in ascending alphabetical order by first name
     */
    public function clientListShowsClientsInAlphaOrder(): void
    {
        // get the divs holding the client details
        $clientDivs = $this->findAllCssElements('h2.govuk-summary-card__title');
        assert(
            count($clientDivs) === count(self::FIRST_NAMES),
            'Expected '.count(self::FIRST_NAMES).' clients, but found '.count($clientDivs)
        );

        // extract the first names
        $actualFirstNames = [];
        foreach ($clientDivs as $clientDiv) {
            $actualFirstNames[] = explode(' ', $clientDiv->getText())[0];
        }

        // ensure they are in the expected order
        $expectedFirstNames = array_merge([], self::FIRST_NAMES);
        sort($expectedFirstNames);

        assert(
            $actualFirstNames === $expectedFirstNames,
            sprintf(
                'Expected order of clients did not match actual order: expected = [%s]; actual = [%s]',
                implode(',', $expectedFirstNames),
                implode(',', $actualFirstNames)
            )
        );
    }
}
