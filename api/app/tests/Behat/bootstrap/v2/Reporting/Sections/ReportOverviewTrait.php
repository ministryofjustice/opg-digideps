<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait ReportOverviewTrait
{
    private array $sectionList = [];

    /**
     * @Then I should see the correct client details
     */
    public function iShouldSeeClientDetails()
    {
        $this->extractSectionHeadersAndContents();

        $deputy = $this->fixtureUsers[0];

        $clientName = $deputy->getClientFirstName().' '.$deputy->getClientLastName();

        $this->assertStringEqualsString(
            $clientName,
            $this->sectionList['Client details']['Name'],
            'Asserting client name found on Overview page');

        $this->assertStringEqualsString(
            $deputy->getClientEmail(),
            $this->sectionList['Client details']['Email'],
            'Asserting client email found on Overview page');

        // Format address into a single string
        $address = '';
        foreach ($deputy->getClientFullAddressArray() as $addressLine) {
            $address = $address.preg_replace("/\r|\n/", ' ', $addressLine).' ';
        }

        $this->assertStringEqualsString(
            trim($address),
            $this->sectionList['Client details']['Address'],
            'Asserting clients address found on Overview page');
    }

    /**
     * @Then I should see the correct deputy details
     */
    public function iShouldSeeDeputyDetails()
    {
        $this->extractSectionHeadersAndContents();

        $deputy = $this->fixtureUsers[0];

        $this->assertStringEqualsString(
            $deputy->getDeputyName(),
            $this->sectionList['Deputy details']['Full Name'],
            'Asserting deputy name found on Overview page');

        // Format address into a single string
        $address = '';
        foreach ($deputy->getDeputyFullAddressArray() as $addressLine) {
            $address = $address.preg_replace("/\r|\n/", ' ', $addressLine).' ';
        }

        $this->assertStringEqualsString(
            trim($address),
            $this->sectionList['Deputy details']['Address'],
            'Asserting deputy address found on Overview page');

        $this->assertStringEqualsString(
            $deputy->getDeputyPhone(),
            $this->sectionList['Deputy details']['Phone'],
            'Asserting deputy phone found on Overview page');

        $this->assertStringEqualsString(
            $deputy->getDeputyEmail(),
            $this->sectionList['Deputy details']['Email address'],
            'Asserting deputy email found on Overview page');

        // Contact email depends on User role
        $role = $deputy->getUserRole();
        if (str_starts_with($role, 'ROLE_PROF')) {
            $email = 'opg.pro@publicguardian.gov.uk';
        } else {
            $email = 'OPG.publicauthorityteam@publicguardian.gov.uk';
        }

        $bannerXPath = "//div[contains(@class, 'moj-banner__message')]";
        $banner = $this->getSession()->getPage()->find('xpath', $bannerXPath);

        $this->assertStringContainsString(
            $email,
            $banner->getText(),
            'Asserting correct contact email found on Overview page');
    }

    private function extractSectionHeadersAndContents()
    {
        // Finding all section headings (via buttons) and section contents
        $buttonsXPath = "//button[contains(@class, 'govuk-accordion__section-button')]";
        $buttons = $this->getSession()->getPage()->findAll('xpath', $buttonsXPath);

        $contentsXPath = "//div[contains(@class, 'govuk-accordion__section-content')]";
        $contents = $this->getSession()->getPage()->findAll('xpath', $contentsXPath);

        $headings = [];

        // Format headings
        foreach ($buttons as $button) {
            $headings[] = trim($button->getText());
        }

        // Every heading will match to a content section
        // Loop through all headings:
        $headingsLength = count($headings);
        for ($i = 0; $i < $headingsLength; ++$i) {
            // Find all dt & dd tags in a sections content
            $descriptionTerms = $contents[$i]->findAll('xpath', '//dt');
            $descriptionDetails = $contents[$i]->findAll('xpath', '//dd');

            // Format dt tags
            $formattedTerms = [];
            foreach ($descriptionTerms as $dt) {
                $formattedTerms[] = trim($dt->getText());
            }

            $descriptionList = [];

            // Match all dts to dds and place into $descriptionList array
            $dtLength = count($formattedTerms);
            for ($x = 0; $x < $dtLength; ++$x) {
                $descriptionList[$formattedTerms[$x]] = trim($descriptionDetails[$x]->getText());
            }

            // Append section and its description list to the $sectionList
            $this->sectionList[$headings[$i]] = $descriptionList;
        }
    }
}
