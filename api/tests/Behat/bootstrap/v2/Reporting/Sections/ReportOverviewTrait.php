<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait ReportOverviewTrait
{
    private array $accordions = [
        'Client details',
        'Deputy details',
        'Current report',
        'Notes',
        'Useful contacts',
        'Submitted reports',
    ];

    /**
     * @Then I should see the correct client details
     */
    public function iShouldSeeClientDetails()
    {
        $deputy = $this->fixtureUsers[0];

        $this->assertPageContainsText($deputy->getClientFirstName().' '.$deputy->getClientLastName());

        $this->assertPageContainsText($deputy->getClientEmail());

        foreach ($deputy->getClientFullAddressArray() as $addressLine) {
            $addressLine = preg_replace("/\r|\n/", ' ', $addressLine);
            $this->assertPageContainsText($addressLine);
        }
    }

    /**
     * @Then I should see the correct deputy details
     */
    public function iShouldSeeDeputyDetails()
    {
        $deputy = $this->fixtureUsers[0];

        $this->assertPageContainsText($deputy->getNamedDeputyName());

        foreach ($deputy->getNamedDeputyFullAddressArray() as $addressLine) {
            $addressLine = preg_replace("/\r|\n/", ' ', $addressLine);
            $this->assertPageContainsText($addressLine);
        }

        $this->assertPageContainsText($deputy->getNamedDeputyPhone());

        $this->assertPageContainsText($deputy->getNamedDeputyEmail());
    }
}
