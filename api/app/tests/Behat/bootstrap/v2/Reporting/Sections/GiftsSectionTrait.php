<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\Reporting\Sections;

use Behat\Step\Then;
use Behat\Step\When;

trait GiftsSectionTrait
{
    private int $giftId = 0;

    public function iViewGiftsSection(): void
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'gifts');
        $this->visitPath($reportSectionUrl);
    }

    #[When('I view and start the gifts report section')]
    public function iViewAndStartGiftsSection(): void
    {
        $this->iViewGiftsSection();
        $this->clickLink('Start gifts');
    }

    public function iChooseNoOnGiftsExistSection(): void
    {
        $this->chooseOption('yes_no[giftsExist]', 'no', 'gifts');
        $this->pressButton('Save and continue');
    }

    public function iChooseYesOnGiftsExistSection(): void
    {
        $this->chooseOption('yes_no[giftsExist]', 'yes', 'gifts');
        $this->pressButton('Save and continue');
    }

    public function iFillGiftDescriptionAndAmount(bool $addAnother): void
    {
        ++$this->giftId;

        $this->fillInField('gifts_single[explanation]', 'random-gift-' . $this->giftId, 'gifts' . $this->giftId);
        $this->fillInFieldTrackTotal('gifts_single[amount]', $this->giftId + 100, 'gifts' . $this->giftId);

        $this->selectOption('gifts_single[addAnother]', $addAnother ? 'yes' : 'no');
    }

    public function iEditGiftDescriptionAndAmount(): void
    {
        $locator = "//td[normalize-space()='random-gift-1']/..";
        $giftRow = $this->getSession()->getPage()->find('xpath', $locator);

        $this->editFieldAnswerInSectionTrackTotal($giftRow, 'gifts_single[amount]', 'gifts1', false);
        $this->editFieldAnswerInSection($giftRow, 'gifts_single[explanation]', $this->faker->sentence(4), 'gifts1', false);
    }

    public function iFollowEditLinkForGifts(): void
    {
        $this->iClickBasedOnAttributeTypeAndValue('a', 'id', 'edit-gifts');
    }

    public function iFollowEditExistsLink(): void
    {
        $urlRegex = '/report\/.*\/gifts\/exist\?from\=summary$/';
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);
    }

    #[When('I have not given any gifts')]
    public function iHaveNotGivenAnyGifts(): void
    {
        $this->iAmOnGiftsExistPage();
        $this->iChooseNoOnGiftsExistSection();

        $this->iAmOnGiftsSummaryPage();
    }

    #[When('I have given multiple gifts')]
    public function iHaveGivenMultipleGifts(): void
    {
        $this->iAmOnGiftsExistPage();
        $this->iChooseYesOnGiftsExistSection();

        // Fill in details for first gift
        $this->iAmOnGiftsAddPage();
        $this->iFillGiftDescriptionAndAmount(true);
        $this->iChooseToSaveAndContinue();

        // Fill in details for second gift
        $this->iAmOnGiftsAddPage();
        $this->iFillGiftDescriptionAndAmount(false);
        $this->iChooseToSaveAndContinue();

        $this->iAmOnGiftsSummaryPage();
    }

    #[When('I change my mind and declare a gift')]
    public function iChangeMyMindAndDeclareGift(): void
    {
        $this->iViewGiftsSection();
        $this->iAmOnGiftsSummaryPage();

        $this->iFollowEditExistsLink();
        $this->iAmOnGiftsExistPage();

        $this->iChooseYesOnGiftsExistSection();
        $this->iAmOnGiftsAddPage();

        $this->iFillGiftDescriptionAndAmount(false);
        $this->iChooseToSaveAndContinue();
        $this->iAmOnGiftsSummaryPage();
    }

    #[When('I edit an existing gift')]
    public function iEditAnExistingGift(): void
    {
        // add a gift
        $this->iViewGiftsSection();
        $this->iFollowEditExistsLink();
        $this->iChooseYesOnGiftsExistSection();
        $this->iFillGiftDescriptionAndAmount(false);
        $this->iChooseToSaveAndContinue();

        // edit the gift
        $this->iGoToReportOverviewUrl();
        $this->iFollowEditLinkForGifts();
        $this->iAmOnGiftsSummaryPage();
        $this->iEditGiftDescriptionAndAmount();
    }

    #[When('I remove the second gift')]
    public function iRemoveTheSecondGift(): void
    {
        $this->removeAnswerFromSection('gifts_single[amount]', 'gifts2', true, 'Yes, remove gift');

        $this->iAmOnGiftsSummaryPage();
    }

    #[When('I remove the first gift')]
    public function iRemoveTheFirstGift(): void
    {
        $this->removeAnswerFromSection('gifts_single[amount]', 'gifts1', true, 'Yes, remove gift');

        $this->iAmOnGiftsStartPage();
    }

    #[Then('I should see the expected gifts report section responses')]
    public function iSeeExpectedGiftsSectionResponses(): void
    {
        $this->expectedResultsDisplayedSimplified(null, false, true, false);
    }
}
