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
     * @When I view the assets report section
     */
    public function iViewAssetsSection()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'assets');
        $this->visitPath($reportSectionUrl);
    }

    /**
     * @When I view and start the assets report section
     */
    public function iViewAndStartAssetsSection()
    {
        $this->iViewAssetsSection();
        $this->clickLink('Start assets');
    }

    /**
     * @When I confirm the client has no assets
     */
    public function iChooseNoOnAssetsExistSection()
    {
        $this->selectOption('yes_no[noAssetToAdd]', '1');
        $this->pressButton('Save and continue');

        array_push($this->assetResponse, ['no']);
    }

    /**
     * @When I confirm the client has assets
     */
    public function iChooseYesOnAssetsExistSection()
    {
        $this->selectOption('yes_no[noAssetToAdd]', '0');
        $this->pressButton('Save and continue');

        array_push($this->assetResponse, ['yes']);
    }

    /**
     * @When I add a single asset
     */
    public function iChooseAssetTypeSection()
    {
        ++$this->assetId;

        //Select Asset Type
        ++$this->assetType;
        $this->iSelectRadioBasedOnChoiceNumber('div', 'data-module', 'govuk-radios', $this->assetType - 1);
        $this->pressButton('Save and continue');

        //Fill out asset value and description
        //TODO if property -> fillOutPropertyDetails
        //
        //TODO else -> fillOutAssetDetails
        $this->iFillAssetDescriptionAndValue($this->assetId, $this->assetType);
        $this->selectOption('add_another[addAnother]', 'no');
        $this->pressButton('Continue');
    }

    private function iFillAssetDescriptionAndValue(int $assetId, int $assetType)
    {
        $formFields = [];

        $this->fillField('asset[value]', $this->assetId + 100);
        $this->fillField('asset[description]', 'asset-'.$this->assetId);

        array_push($formFields, 'asset-'.$this->assetId);
        array_push($formFields, '-');
        array_push($formFields, '£'.number_format($assetId + 100, 2, '.', ','));

        $this->pressButton('Save and continue');

        //Antiques, artwork & jewellery are grouped into one summary list
        if (in_array($assetType, [$this->ANTIQUE_ASSET_TYPE, $this->ARTWORK_ASSET_TYPE, $this->JEWELLERY_ASSET_TYPE])) {
            if (null === $this->assetDetails[0]) {
                $this->assetDetails[0][] = $formFields;
            } else {
                array_push($this->assetDetails[0][], $formFields);
            }
        } else {
            if (null === $this->assetDetails[$assetType - 1]) {
                $this->assetDetails[$assetType - 1][] = $formFields;
            } else {
                array_push($this->assetDetails[$assetType - 1], $formFields);
            }
        }
    }

    /**
     * @When I add a single property asset
     */
    public function iChoosePropertyAsset()
    {
        ++$this->assetId;

        //Select Property Asset Type
        $this->assetType = $this->PROPERTY_ASSET_TYPE;
        $this->iSelectRadioBasedOnChoiceNumber('div', 'data-module', 'govuk-radios', $this->assetType - 1);
        $this->pressButton('Save and continue');

        $this->iFillPropertyDetailsAndValue($this->assetId);
        $this->selectOption('add_another[addAnother]', 'no');
        $this->pressButton('Continue');
    }

    private function iFillPropertyDetailsAndValue(int $assetId)
    {
        $formFields = [];

        // fill address fields
        $streetAddress = $this->faker->streetAddress;
        $postcode = $this->faker->postcode;
        //array_push($formFields, $streetAddress.','.$postcode);

        $this->fillField('asset[address]', $streetAddress);
        $this->fillField('asset[postcode]', $postcode);
        $this->pressButton('Save and continue');

        // fill occupancy text box
        $occupants = $this->faker->text(50);
        array_push($formFields, [$occupants]);

        $this->fillField('asset[occupants]', $occupants);
        $this->pressButton('Save and continue');

        // select partial ownership radio option & fill owned percentage field
        $ownedPercentage = $this->faker->numberBetween(1, 99);
        array_push($formFields, ['Partly owned']);
        array_push($formFields, [$ownedPercentage.'%']);

        $this->selectOption('asset[owned]', 'partly');
        $this->fillField('asset[ownedPercentage]', $ownedPercentage);
        $this->pressButton('Save and continue');

        // select has mortgage radio option & fill mortgage value field
        $mortgageValue = $this->faker->numberBetween(1000, 10000);
        array_push($formFields, ['Yes']);
        array_push($formFields, ['£'.number_format($mortgageValue, 2, '.', ',')]);

        $this->selectOption('asset[hasMortgage]', 'yes');
        $this->fillField('asset[mortgageOutstandingAmount]', $mortgageValue);
        $this->pressButton('Save and continue');

        // fill asset value field
        $assetValue = $this->faker->numberBetween(1000, 10000);
        array_push($formFields, ['£'.number_format($assetValue, 2, '.', ',')]);

        $this->fillField('asset[value]', $assetValue);
        $this->pressButton('Save and continue');

        // select no equity release scheme radio option
        array_push($formFields, ['No']);

        $this->selectOption('asset_isSubjectToEquityRelease_1', 'no');
        $this->pressButton('Save and continue');

        // select no charges radio option
        array_push($formFields, ['No']);

        $this->selectOption('asset[hasCharges]', 'no');
        $this->pressButton('Save and continue');

        // select property rented out option & fill additional fields
        $endMonth = $this->faker->numberBetween(1, 12);
        $endYear = $this->faker->numberBetween(2000, 2050);
        $rent = $this->faker->numberBetween(100, 1000);

        array_push($formFields, ['Yes']);
        $month_name = date('F', mktime(0, 0, 0, $endMonth, 10));
        array_push($formFields, [$month_name.' '.$endYear]);
        array_push($formFields, ['£'.number_format($rent, 2, '.', ',')]);

        $this->selectOption('asset[isRentedOut]', 'yes');
        $this->fillField('asset[rentAgreementEndDate][month]', $endMonth);
        $this->fillField('asset[rentAgreementEndDate][year]', $endYear);
        $this->fillField('asset_rentIncomeMonth', $rent);
        $this->pressButton('Save and continue');

        // save fields
        if (null === $this->assetDetails[$this->PROPERTY_ASSET_TYPE - 1]) {
            $this->assetDetails[$this->PROPERTY_ASSET_TYPE - 1][] = $formFields;
        } else {
            array_push($this->assetDetails[$this->PROPERTY_ASSET_TYPE - 1][], $formFields);
        }
    }

    /**
     * @Then I should see the expected assets report section responses
     */
    public function iSeeExpectedAssetsSectionResponses()
    {
        $sectionNumber = 0;
        if ($this->assetResponse[0] == ['yes']) {
            $this->calculateAssetTotalsForSections();
            $this->expectedResultsDisplayed($sectionNumber, $this->assetResponse, 'Asset Answers to Questions');
            ++$sectionNumber;
            foreach ($this->assetDetails as $index => $sectionAssets) {
                if ($index == $this->PROPERTY_ASSET_TYPE - 1) {
                    $this->expectedResultsDisplayed($sectionNumber, $sectionAssets[0], 'Asset Details');
                } else {
                    $this->expectedResultsDisplayed($sectionNumber, $sectionAssets, 'Asset Details');
                }
                ++$sectionNumber;
            }
        } else {
            $this->expectedResultsDisplayed(0, $this->assetResponse, 'Asset Answers to Questions');
        }
    }

    private function calculateAssetTotalsForSections()
    {
        foreach ($this->assetDetails as $index => $assetSection) {
            $total = 0;
            if ($index == $this->PROPERTY_ASSET_TYPE - 1) {
//                foreach ($assetSection as $property) {
//                    $ownership = '100';
//                    if ($property[2] == 'Partly owned') {
//                        $ownership = floatval(mb_substr($property[3], 0, 2));
//                    }
//                    $value = floatval(mb_substr($property[6], 1));
//                    $total += $value * $ownership /  100;
//                }
            } else {
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
                array_push($this->assetDetails[$index], ['£'.number_format($total, 2, '.', ',')]);
            }
        }
    }
}
