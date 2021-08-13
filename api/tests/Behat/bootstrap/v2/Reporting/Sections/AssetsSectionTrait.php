<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

use Throwable;

trait AssetsSectionTrait
{
    private array $combinedAssetTypes = ['Antiques', 'Artwork', 'Jewellery'];

    private array $assetDictionary = [
        1 => 'Antiques',
        2 => 'Artwork',
        3 => 'Investment bonds',
        4 => 'Jewellery',
        5 => 'National Savings certificates',
        6 => 'Premium Bonds',
        7 => 'Property',
        8 => 'Stocks and shares',
        9 => 'Unit trusts',
        10 => 'Vehicles',
        11 => 'Assets held outside England and Wales',
        12 => 'Other valuable assets',
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
     * @When I add :numberOfAssets asset(s)
     */
    public function iAddNumberOfAssets(int $numberOfAssets)
    {
        $this->iAmOnAssetTypePage();

        $assetRange = range(1, $numberOfAssets);

        foreach ($assetRange as $propertyNumber) {
            $assetType = $this->assetDictionary[$propertyNumber];

            if ('Property' === $assetType) {
                continue;
            }

            $formSectionName = 'assetType'.$assetType;

            if (in_array($assetType, $this->combinedAssetTypes)) {
                $formSectionName = 'assetTypeArtworkAntiquesJewellery';
                // Artwork, antiques and jewellery are grouped under one section on summary page
                $this->chooseOption('asset_title[title]', $assetType, $formSectionName, 'Artwork, antiques and jewellery');
            } else {
                $this->chooseOption('asset_title[title]', $assetType, $formSectionName);
            }

            $this->pressButton('Save and continue');

            $this->iFillAssetDescriptionAndValue($assetType);

            $this->iAmOnAddAnotherAssetPage();

            $this->selectOption('add_another[addAnother]', $numberOfAssets === $propertyNumber ? 'no' : 'yes');
            $this->pressButton('Continue');
        }

        // We only need to assert on the combined section title once so removing the duplicates
        if ($combinedSectionTitles = $this->getSectionAnswers('assetTypeArtworkAntiquesJewellery')) {
            $this->submittedAnswersByFormSections['assetTypeArtworkAntiquesJewellery'] = [$combinedSectionTitles[0]];
        }
    }

    private function iFillAssetDescriptionAndValue(string $assetType)
    {
        $this->fillInFieldTrackTotal('asset[value]', mt_rand(5, 2000), 'asset-'.$assetType);
        $this->fillInField('asset[description]', $this->faker->sentence(5, 25), 'asset-'.$assetType);

        $this->pressButton('Save and continue');
    }

    /**
     * @When I add :numberOfProperties property asset(s)
     */
    public function iAddNumberOfPropertyAsset(int $numberOfProperties)
    {
        try {
            $this->iAmOnAssetTypePage();
        } catch (Throwable $e) {
            $this->clickLink('Add an asset');
            $this->iAmOnAssetTypePage();
        }

        $propertyRange = range(1, $numberOfProperties);

        foreach ($propertyRange as $propertyNumber) {
            $assetType = $this->assetDictionary[7];

            $this->chooseOption('asset_title[title]', $assetType, 'assetType'.$assetType, $assetType.' '.$propertyNumber);
            $this->pressButton('Save and continue');

            $this->iFillPropertyDetailsAndValue();

            $this->iAmOnAddAnotherAssetPage();

            $this->selectOption('add_another[addAnother]', $numberOfProperties === $propertyNumber ? 'no' : 'yes');
            $this->pressButton('Continue');
        }
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
     * @Then I should see the expected assets report section responses
     */
    public function iSeeExpectedAssetsSectionResponses()
    {
        $this->iAmOnAssetsSummaryPage();

        $properties = $this->getSectionAnswers('assetDetailsPropertyPercentage') ?: [];

        foreach ($properties as $index => $propertyPercentageAnswers) {
            if ('Partly owned' === $this->getSectionAnswers('assetDetailsPropertyPercentage')[$index]['asset[owned]']) {
                $propertyValue = $this->getSectionAnswers('assetDetailsPropertyValue')[$index]['asset[value]'];
                $ownedPercentage = $this->getSectionAnswers('assetDetailsPropertyPercentage')[$index]['asset[ownedPercentage]'];

                $this->subtractFromGrandTotal($propertyValue);

                $this->addToGrandTotal($propertyValue * intval($ownedPercentage) / 100);
            }
        }
    }
}
