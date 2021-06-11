<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait AssetsSectionTrait
{
    private int $assetType = 0;
    private int $assetId = 0;
    private array $assetDetails = [];
    private array $assetResponse = [];

    private int $ANTIQUE_ASSET_TYPE = 1;
    private int $ARTWORK_ASSET_TYPE = 2;
    private int $JEWELLERY_ASSET_TYPE = 4;
    private int $PROPERTY_ASSET_TYPE = 7;

    /**
     * @When I visit and start the assets report section
     */
    public function iViewAndStartAssetsSection()
    {
        $this->iVisitAssetsSection();
        $this->clickLink('Start assets');
    }

    /**
     * @When I confirm the client has no assets
     */
    public function iChooseNoOnAssetsExistSection()
    {
        $this->iAmOnAssetsExistPage();

        $this->selectOption('yes_no[noAssetToAdd]', '1');
        $this->pressButton('Save and continue');

        $this->assetResponse[] = ['no'];
    }

    /**
     * @When I confirm the client has assets
     */
    public function iChooseYesOnAssetsExistSection()
    {
        $this->iAmOnAssetsExistPage();

        $this->selectOption('yes_no[noAssetToAdd]', '0');
        $this->pressButton('Save and continue');

        $this->assetResponse[] = ['yes'];
    }

    /**
     * @When I add a single asset
     */
    public function iAddSingleAsset()
    {
        $this->iAmOnAssetTypePage();

        ++$this->assetId;

        //Select type of asset
        ++$this->assetType;
        $this->iSelectRadioBasedOnChoiceNumber('div', 'data-module', 'govuk-radios', $this->assetType - 1);
        $this->pressButton('Save and continue');

        //Fill out details about asset
        $this->iFillAssetDescriptionAndValue($this->assetId, $this->assetType);

        $this->iAmOnAddAnotherAssetPage();

        $this->selectOption('add_another[addAnother]', 'no');
        $this->pressButton('Continue');
    }

    private function iFillAssetDescriptionAndValue(int $assetId, int $assetType)
    {
        $formFields = [];

        $this->fillField('asset[value]', $this->assetId + 100);
        $this->fillField('asset[description]', 'asset-'.$this->assetId);

        $formFields[] = 'asset-'.$this->assetId;
        $formFields[] = '-';
        $formFields[] = '£'.number_format($assetId + 100, 2, '.', ',');

        $this->pressButton('Save and continue');

        //Antiques, artwork & jewellery assets are grouped into one summary list
        if (in_array($assetType, [$this->ANTIQUE_ASSET_TYPE, $this->ARTWORK_ASSET_TYPE, $this->JEWELLERY_ASSET_TYPE])) {
            if (null === $this->assetDetails[0]) {
                $this->assetDetails[0][] = $formFields;
            } else {
                array_unshift($this->assetDetails[0], $formFields);
            }
        } else {
            if (null === $this->assetDetails[$assetType - 1]) {
                $this->assetDetails[$assetType - 1][] = $formFields;
            } else {
                array_unshift($this->assetDetails[$assetType - 1], $formFields);
            }
        }
    }

    /**
     * @When I add 12 assets including a property
     */
    public function iAddMultipleAssets()
    {
        $this->iAmOnAssetTypePage();

        $this->assetType = 0;
        //Adding one of each type of asset
        while ($this->assetType <= 11) {
            ++$this->assetId;
            ++$this->assetType;

            //After adding the first asset, confirm you want to add more assets
            if ($this->assetType > 1) {
                $this->iAmOnAddAnotherAssetPage();
                $this->selectOption('add_another[addAnother]', 'yes');
                $this->pressButton('Continue');
            }

            //Select type of asset
            $this->iSelectRadioBasedOnChoiceNumber('div', 'data-module', 'govuk-radios', $this->assetType - 1);
            $this->pressButton('Save and continue');

            if ($this->assetType == $this->PROPERTY_ASSET_TYPE) {
                $this->iFillPropertyDetailsAndValue($this->assetId);
            } else {
                $this->iFillAssetDescriptionAndValue($this->assetId, $this->assetType);
            }
        }

        $this->iAmOnAddAnotherAssetPage();

        $this->selectOption('add_another[addAnother]', 'no');
        $this->pressButton('Continue');
    }

    /**
     * @When I add a single property asset
     */
    public function iAddPropertyAsset()
    {
        $this->iAmOnAssetTypePage();

        ++$this->assetId;

        //Select Property asset type
        $this->assetType = $this->PROPERTY_ASSET_TYPE;
        $this->iSelectRadioBasedOnChoiceNumber('div', 'data-module', 'govuk-radios', $this->assetType - 1);
        $this->pressButton('Save and continue');

        $this->iFillPropertyDetailsAndValue($this->assetId);

        $this->iAmOnAddAnotherAssetPage();

        $this->selectOption('add_another[addAnother]', 'no');
        $this->pressButton('Continue');
    }

    private function iFillPropertyDetailsAndValue(int $assetId)
    {
        $formFields = [];

        // fill address fields
        $streetAddress = $this->faker->streetAddress;
        $streetAddress = str_replace(["\n", "\r"], ' ', $streetAddress);
        $postcode = $this->faker->postcode;
        $formFields[] = [$streetAddress.', '.$postcode];

        $this->fillField('asset[address]', $streetAddress);
        $this->fillField('asset[postcode]', $postcode);
        $this->pressButton('Save and continue');

        // fill occupancy text box
        $occupants = $this->faker->text(50);
        $formFields[] = [$occupants];

        $this->fillField('asset[occupants]', $occupants);
        $this->pressButton('Save and continue');

        // select partial ownership radio option & fill owned percentage field
        $ownedPercentage = $this->faker->numberBetween(1, 99);
        $formFields[] = ['Partly owned'];
        $formFields[] = [$ownedPercentage.'%'];

        $this->selectOption('asset[owned]', 'partly');
        $this->fillField('asset[ownedPercentage]', $ownedPercentage);
        $this->pressButton('Save and continue');

        // select has mortgage radio option & fill mortgage value field
        $mortgageValue = $this->faker->numberBetween(1000, 10000);
        $formFields[] = ['Yes'];
        $formFields[] = ['£'.number_format($mortgageValue, 2, '.', ',')];

        $this->selectOption('asset[hasMortgage]', 'yes');
        $this->fillField('asset[mortgageOutstandingAmount]', $mortgageValue);
        $this->pressButton('Save and continue');

        // fill asset value field
        $assetValue = $this->faker->numberBetween(1000, 10000);
        $formFields[] = ['£'.number_format($assetValue, 2, '.', ',')];

        $this->fillField('asset[value]', $assetValue);
        $this->pressButton('Save and continue');

        // select no equity release scheme radio option
        $formFields[] = ['No'];

        $this->selectOption('asset_isSubjectToEquityRelease_1', 'no');
        $this->pressButton('Save and continue');

        // select no charges radio option
        $formFields[] = ['No'];

        $this->selectOption('asset[hasCharges]', 'no');
        $this->pressButton('Save and continue');

        // select property rented out option & fill additional fields
        $endMonth = $this->faker->numberBetween(1, 12);
        $endYear = $this->faker->numberBetween(2000, 2050);
        $rent = $this->faker->numberBetween(100, 1000);

        $formFields[] = ['Yes'];
        $month_name = date('F', mktime(0, 0, 0, $endMonth, 10));
        $formFields[] = [$month_name.' '.$endYear];
        $formFields[] = ['£'.number_format($rent, 2, '.', ',')];

        $this->selectOption('asset[isRentedOut]', 'yes');
        $this->fillField('asset[rentAgreementEndDate][month]', $endMonth);
        $this->fillField('asset[rentAgreementEndDate][year]', $endYear);
        $this->fillField('asset_rentIncomeMonth', $rent);
        $this->pressButton('Save and continue');

        // save fields
        if (null === $this->assetDetails[$this->PROPERTY_ASSET_TYPE - 1]) {
            $this->assetDetails[$this->PROPERTY_ASSET_TYPE - 1][] = $formFields;
        } else {
            array_unshift($this->assetDetails[$this->PROPERTY_ASSET_TYPE - 1], $formFields);
        }
    }

    /**
     * @When I add 3 property assets
     */
    public function iAddMultiplePropertyAssets()
    {
        ++$this->assetId;

        //Select Property asset type
        $this->assetType = $this->PROPERTY_ASSET_TYPE;
        for ($i = 0; $i <= 2; ++$i) {
            $this->iAmOnAssetTypePage();
            $this->iSelectRadioBasedOnChoiceNumber('div', 'data-module', 'govuk-radios', $this->assetType - 1);
            $this->pressButton('Save and continue');
            $this->iFillPropertyDetailsAndValue($this->assetId);

            if (2 == $i) {
                $this->iAmOnAddAnotherAssetPage();
                $this->selectOption('add_another[addAnother]', 'no');
                $this->pressButton('Continue');
            } else {
                $this->iAmOnAddAnotherAssetPage();
                $this->selectOption('add_another[addAnother]', 'yes');
                $this->pressButton('Continue');
            }
        }
    }

    /**
     * @Then I should see the expected assets report section responses
     */
    public function iSeeExpectedAssetsSectionResponses()
    {
        $this->iAmOnAssetsSummaryPage();

        $sectionNumber = 0;
        if ($this->assetResponse[0] == ['yes']) {
            $this->expectedResultsDisplayed($sectionNumber, $this->assetResponse, 'Asset Answers to Questions');
            ++$sectionNumber;

            //Sort asset sections into the order they appear on the summary page
            $sortedResults = $this->sortAssetsAndSections();
            //Calculate the total value of assets for each asset section
            $sortedResults = $this->calculateAssetTotalsForSections($sortedResults);

            //Loop through each asset section
            foreach ($sortedResults as $index => $sectionAssets) {
                if ($index == $this->PROPERTY_ASSET_TYPE - 1) {
                    $this->expectedResultsDisplayed($sectionNumber, $sectionAssets[0], 'Asset Details');
                } else {
                    $this->expectedResultsDisplayed($sectionNumber, $sectionAssets, 'Asset Details');
                }
                ++$sectionNumber;
            }

            //Calculate total value of all assets
            $expectedTotalValue = $this->calculateTotalValueOfAssets($sortedResults);
            $formattedExpectedTotalValue = '£'.number_format($expectedTotalValue, 2, '.', ',');

            $xpath = sprintf('//div[contains(text(),"Total value of assets:")]');
            $foundTotalValue = $this->getSession()->getPage()->find('xpath', $xpath)->getHtml();

            //Assert expected total value of all assets matches found total value of all assets
            $validTotal = str_contains($foundTotalValue, strval($formattedExpectedTotalValue));

            if (!$validTotal) {
                $this->throwContextualException(
                    sprintf('Total value of assets does not match. Found Total Value: %s but expected Total Value is %s',
                            $foundTotalValue,
                            $formattedExpectedTotalValue));
            }
        } else {
            $this->expectedResultsDisplayed(0, $this->assetResponse, 'Asset Answers to Questions');
        }
    }

    private function calculateAssetTotalsForSections(array $sortedAssetDetails)
    {
        foreach ($sortedAssetDetails as $index => $assetSection) {
            $total = 0;
            //Property summary list does not contain total value of all properties
            if ($index != $this->PROPERTY_ASSET_TYPE - 1) {
                foreach ($assetSection as $asset) {
                    foreach ($asset as $value) {
                        if (str_starts_with($value, '£')) {
                            $assetValue = mb_substr($value, 1);
                            $total += floatval($assetValue);
                        }
                    }
                }
            }
            if ($total > 0) {
                array_push($sortedAssetDetails[$index], ['£'.number_format($total, 2, '.', ',')]);
            }
        }

        return $sortedAssetDetails;
    }

    private function calculateTotalValueOfAssets(array $sortedAssetDetails)
    {
        $total = 0;
        foreach ($sortedAssetDetails as $index => $assetSection) {
            if ($index == $this->PROPERTY_ASSET_TYPE - 1) {
                //Total value of properties is based on the clients share in the property
                foreach ($assetSection as $property) {
                    $ownership = '100';
                    if ('Partly owned' == $property[2][0]) {
                        $ownership = floatval($property[3][0]);
                    }
                    $propertyValue = mb_substr($property[6][0], 1);
                    $value = (float) str_replace(',', '', $propertyValue);
                    $total += $value * $ownership / 100;
                }
            } else {
                $sectionTotalString = end(end($assetSection));
                $sectionTotal = mb_substr($sectionTotalString, 1);
                $total += (float) str_replace(',', '', $sectionTotal);
            }
        }

        return $total;
    }

    private function sortAssetsAndSections()
    {
        $sortedResults = $this->assetDetails;

        //Assets outside of England & Wales section appears 2nd
        if (null != $sortedResults[10]) {
            $sortedResults[1] = $sortedResults[10];
            unset($sortedResults[10]);
        }
        //Other valuable assets section appears between National Savings certificates & Premium Bonds Sections
        if (null != $sortedResults[11]) {
            if (null != $sortedResults[4]) {
                $sortedResults[3] = $sortedResults[4];
                $sortedResults[4] = $sortedResults[11];
                unset($sortedResults[11]);
            } else {
                $sortedResults[3] = $sortedResults[11];
                unset($sortedResults[11]);
            }
        }
        ksort($sortedResults);

        return $sortedResults;
    }
}
