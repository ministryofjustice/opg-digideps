<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait GiftsSectionTrait
{
    private int $giftId = 0;

    /**
     * @When I view the gifts report section
     */
    public function iViewGiftsSection()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'gifts');
        $this->visitPath($reportSectionUrl);
    }

    /**
     * @When I view and start the gifts report section
     */
    public function iViewAndStartGiftsSection()
    {
        $this->iViewGiftsSection();
        $this->clickLink('Start gifts');
    }

    /**
     * @When I choose no and save on gifts exist section
     */
    public function iChooseNoOnGiftsExistSection()
    {
        $this->chooseOption('yes_no[giftsExist]', 'no', 'gifts');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I choose yes and save on gifts exist section
     */
    public function iChooseYesOnGiftsExistSection()
    {
        $this->chooseOption('yes_no[giftsExist]', 'yes', 'gifts');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I fill in gift description and amount
     */
    public function iFillGiftDescriptionAndAmount()
    {
        ++$this->giftId;

        $this->fillInField('gifts_single[explanation]', 'random-gift-'.$this->giftId, 'gifts'.$this->giftId);
        $this->fillInFieldTrackTotal('gifts_single[amount]', $this->giftId + 100, 'gifts'.$this->giftId);
    }

    /**
     * @When I edit first gift description and amount
     */
    public function iEditGiftDescriptionAndAmount()
    {
        $locator = "//td[normalize-space()='random-gift-1']/..";
        $giftRow = $this->getSession()->getPage()->find('xpath', $locator);

        $this->editFieldAnswerInSectionTrackTotal($giftRow, 'gifts_single[amount]', 'gifts1', false);
        $this->editFieldAnswerInSection($giftRow, 'gifts_single[explanation]', $this->faker->sentence(4), 'gifts1', false);
    }

    /**
     * @When I follow edit link for gifts section
     */
    public function iFollowEditLinkForGifts()
    {
        $this->iClickBasedOnAttributeTypeAndValue('a', 'id', 'edit-gifts');
    }

    /**
     * @When I follow the edit link for whether gifts exist
     */
    public function iFollowEditExistsLink()
    {
        $urlRegex = '/report\/.*\/gifts\/exist\?from\=summary$/';
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);
    }

    /**
     * @When I have not given any gifts
     */
    public function iHaveNotGivenAnyGifts()
    {
        $this->iAmOnGiftsExistPage();
        $this->iChooseNoOnGiftsExistSection();

        $this->iAmOnGiftsSummaryPage();
    }

    /**
     * @When I have given multiple gifts
     */
    public function iHaveGivenMultipleGifts()
    {
        $this->iAmOnGiftsExistPage();
        $this->iChooseYesOnGiftsExistSection();

        // Fill in details for first gift
        $this->iAmOnGiftsAddPage();
        $this->iFillGiftDescriptionAndAmount();
        $this->iChooseToSaveAndAddAnother();

        // Fill in details for second gift
        $this->iAmOnGiftsAddPage();
        $this->iFillGiftDescriptionAndAmount();
        $this->iChooseToSaveAndContinue();

        $this->iAmOnGiftsSummaryPage();
    }

    /**
     * @When I change my mind and declare a gift
     */
    public function iChangeMyMindAndDeclareGift()
    {
        $this->iViewGiftsSection();
        $this->iAmOnGiftsSummaryPage();

        $this->iFollowEditExistsLink();
        $this->iAmOnGiftsExistPage();

        $this->iChooseYesOnGiftsExistSection();
        $this->iAmOnGiftsAddPage();

        $this->iFillGiftDescriptionAndAmount();
        $this->iChooseToSaveAndContinue();
        $this->iAmOnGiftsSummaryPage();
    }

    /**
     * @When I edit an existing gift
     */
    public function iEditAnExistingGift()
    {
        // add a gift
        $this->iViewGiftsSection();
        $this->iFollowEditExistsLink();
        $this->iChooseYesOnGiftsExistSection();
        $this->iFillGiftDescriptionAndAmount();
        $this->iChooseToSaveAndContinue();

        // edit the gift
        $this->iGoToReportOverviewUrl();
        $this->iFollowEditLinkForGifts();
        $this->iAmOnGiftsSummaryPage();
        $this->iEditGiftDescriptionAndAmount();
    }

    /**
     * @When I remove the second gift
     */
    public function iRemoveTheSecondGift()
    {
        $this->removeAnswerFromSection('gifts_single[amount]', 'gifts2', true, 'Yes, remove gift');

        $this->iAmOnGiftsSummaryPage();
    }

    /**
     * @When I remove the first gift
     */
    public function iRemoveTheFirstGift()
    {
        $this->removeAnswerFromSection('gifts_single[amount]', 'gifts1', true, 'Yes, remove gift');

        $this->iAmOnGiftsStartPage();
    }

    /**
     * @Then I should see the expected gifts report section responses
     */
    public function iSeeExpectedGiftsSectionResponses()
    {
        $this->expectedResultsDisplayedSimplified(null, false, true, false);
    }
}
