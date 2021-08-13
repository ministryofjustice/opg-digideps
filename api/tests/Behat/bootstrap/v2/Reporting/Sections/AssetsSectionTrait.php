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

    private array $combinedAssetTypes = ['Antiques', 'Artwork', 'Jewellery'];

    private array $assetDictionary = [
        0 => 'Antiques',
        1 => 'Artwork',
        2 => 'Investment bonds',
        3 => 'Jewellery',
        4 => 'National Savings certificates',
        5 => 'Premium Bonds',
        6 => 'Property',
        7 => 'Stocks and shares',
        8 => 'Unit trusts',
        9 => 'Vehicles',
        10 => 'Assets held outside England and Wales',
        11 => 'Other valuable assets',
    ];

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

        $this->chooseOption('yes_no[noAssetToAdd]', '1', 'assetsToAdd', 'No');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I confirm the client has assets
     */
    public function iChooseYesOnAssetsExistSection()
    {
        $this->iAmOnAssetsExistPage();

        $this->chooseOption('yes_no[noAssetToAdd]', '0', 'assetsToAdd', 'Yes');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I add a single asset
     */
    public function iAddSingleAsset()
    {
        $this->iAmOnAssetTypePage();

        $assetType = $this->assetDictionary[0];
        $translation = !in_array($assetType, $this->combinedAssetTypes) ?: 'Artwork, antiques and jewellery';

        $this->chooseOption('asset_title[title]', $assetType, 'assetType'.$assetType, $translation);
        $this->pressButton('Save and continue');

        //Fill out details about asset
        $this->iFillAssetDescriptionAndValue($assetType);

        $this->iAmOnAddAnotherAssetPage();

        $this->selectOption('add_another[addAnother]', 'no');
        $this->pressButton('Continue');
    }

    private function iFillAssetDescriptionAndValue(string $assetType)
    {
        $this->fillInFieldTrackTotal('asset[value]', mt_rand(5, 2000), 'asset-'.$assetType);
        $this->fillInField('asset[description]', $this->faker->sentence(5, 25), 'asset-'.$assetType);

//        $formFields[] = 'asset-'.$this->assetId;
//        $formFields[] = '-';
//        $formFields[] = '£'.number_format($assetId + 100, 2, '.', ',');

        $this->pressButton('Save and continue');

//        //Antiques, artwork & jewellery assets are grouped into one summary list
//        if (in_array($assetType, [$this->ANTIQUE_ASSET_TYPE, $this->ARTWORK_ASSET_TYPE, $this->JEWELLERY_ASSET_TYPE])) {
//            if (null === $this->assetDetails[0]) {
//                $this->assetDetails[0][] = $formFields;
//            } else {
//                array_unshift($this->assetDetails[0], $formFields);
//            }
//        } else {
//            if (null === $this->assetDetails[$assetType - 1]) {
//                $this->assetDetails[$assetType - 1][] = $formFields;
//            } else {
//                array_unshift($this->assetDetails[$assetType - 1], $formFields);
//            }
//        }
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

        $assetType = $this->assetDictionary[6];

        $this->chooseOption('asset_title[title]', $assetType, 'assetType'.$assetType, $assetType.' 1');
        $this->pressButton('Save and continue');

        $this->iFillPropertyDetailsAndValue();

        $this->iAmOnAddAnotherAssetPage();

        $this->selectOption('add_another[addAnother]', 'no');
        $this->pressButton('Continue');
    }

    private function iFillPropertyDetailsAndValue()
    {
        $streetAddress = $this->faker->streetAddress;
        $streetAddress = str_replace(["\n", "\r"], ' ', $streetAddress);
        $postcode = $this->faker->postcode;

        $this->fillInField('asset[address]', $streetAddress, 'assetDetailsPropertyAddress');
        $this->fillInField('asset[postcode]', $postcode, 'assetDetailsPropertyAddress');
        $this->pressButton('Save and continue');

        $this->fillInField('asset[occupants]', $this->faker->text(50), 'assetDetailsPropertyOccupants');
        $this->pressButton('Save and continue');

        $this->chooseOption('asset[owned]', 'partly', 'assetDetailsPropertyPercentage', 'Partly owned');

        $percentage = mt_rand(1, 99);
        $this->fillInField('asset[ownedPercentage]', $percentage, 'assetDetailsPropertyPercentage', $percentage.'%');

        $this->pressButton('Save and continue');

        $this->ChooseOption('asset[hasMortgage]', 'yes', 'assetDetailsPropertyMortgage');
        $this->fillInField('asset[mortgageOutstandingAmount]', mt_rand(10000, 100000));
        $this->pressButton('Save and continue');

        $this->fillInFieldTrackTotal('asset[value]', mt_rand(100000, 200000), 'assetDetailsPropertyValue');
        $this->pressButton('Save and continue');

        $this->chooseOption('asset_isSubjectToEquityRelease_1', 'no', 'assetDetailsPropertyEquityRelease');
        $this->pressButton('Save and continue');

        $this->chooseOption('asset[hasCharges]', 'no', 'assetDetailsPropertyCharges');
        $this->pressButton('Save and continue');

        $this->chooseOption('asset[isRentedOut]', 'yes', 'assetDetailsPropertyRented');
        $this->fillInDateFields('asset[rentAgreementEndDate]', null, mt_rand(1, 12), mt_rand(2018, 2028), 'assetDetailsPropertyRented');
        $this->fillInField('asset_rentIncomeMonth', mt_rand(100, 1000), 'assetDetailsPropertyRented');
        $this->pressButton('Save and continue');
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

        $properties = $this->getSectionAnswers('assetDetailsPropertyPercentage') ?: [];

        foreach ($properties as $index => $propertyPercentageAnswers) {
            if ('Partly owned' === $this->getSectionAnswers('assetDetailsPropertyPercentage')[$index]['asset[owned]']) {
                $propertyValue = $this->getSectionTotal('assetDetailsPropertyValue');
                $ownedPercentage = $this->getSectionAnswers('assetDetailsPropertyPercentage')[$index]['asset[ownedPercentage]'];

                $this->subtractFromGrandTotal($propertyValue);

                $this->addToGrandTotal($propertyValue * intval($ownedPercentage) / 100);
            }
        }

        var_dump($this->submittedAnswersByFormSections);
        $this->expectedResultsDisplayedSimplified();

//        $sectionNumber = 0;
//        if ($this->assetResponse[0] == ['yes']) {
//            $this->expectedResultsDisplayed($sectionNumber, $this->assetResponse, 'Asset Answers to Questions');
//            ++$sectionNumber;
//
//            //Sort asset sections into the order they appear on the summary page
//            $sortedResults = $this->sortAssetsAndSections();
//            //Calculate the total value of assets for each asset section
//            $sortedResults = $this->calculateAssetTotalsForSections($sortedResults);
//
//            //Loop through each asset section
//            foreach ($sortedResults as $index => $sectionAssets) {
//                if ($index == $this->PROPERTY_ASSET_TYPE - 1) {
//                    $this->expectedResultsDisplayed($sectionNumber, $sectionAssets[0], 'Asset Details');
//                } else {
//                    $this->expectedResultsDisplayed($sectionNumber, $sectionAssets, 'Asset Details');
//                }
//                ++$sectionNumber;
//            }
//
//            //Calculate total value of all assets
//            $expectedTotalValue = $this->calculateTotalValueOfAssets($sortedResults);
//            $formattedExpectedTotalValue = '£'.number_format($expectedTotalValue, 2, '.', ',');
//
//            $xpath = sprintf('//div[contains(text(),"Total value of assets:")]');
//            $foundTotalValue = $this->getSession()->getPage()->find('xpath', $xpath)->getHtml();
//
//            //Assert expected total value of all assets matches found total value of all assets
//            $validTotal = str_contains($foundTotalValue, strval($formattedExpectedTotalValue));
//
//            if (!$validTotal) {
//                $this->throwContextualException(
//                    sprintf('Total value of assets does not match. Found Total Value: %s but expected Total Value is %s',
//                            $foundTotalValue,
//                            $formattedExpectedTotalValue));
//            }
//        } else {
//            $this->expectedResultsDisplayed(0, $this->assetResponse, 'Asset Answers to Questions');
//        }
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
