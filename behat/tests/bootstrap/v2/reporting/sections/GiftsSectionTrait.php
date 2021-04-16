<?php declare(strict_types=1);

namespace DigidepsBehat\v2\Reporting\Sections;

trait GiftsSectionTrait
{
    private int $giftId = 0;
    private array $giftDetails = [];

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
        $this->selectOption('yes_no[giftsExist]', 'no');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I choose yes and save on gifts exist section
     */
    public function iChooseYesOnGiftsExistSection()
    {
        $this->selectOption('yes_no[giftsExist]', 'yes');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I fill in gift description and amount
     */
    public function iFillGiftDescriptionAndAmount()
    {
        $formFields = [];
        $this->giftId += 1;

        $this->fillField('gifts_single[explanation]', 'random-gift-' . $this->giftId);
        array_push($formFields, 'random-gift-' . $this->giftId);

        if ($this->elementExistsOnPage('select', 'id', 'gifts_single_bankAccountId')) {
            $choiceMade = $this->iSelectBasedOnChoiceNumber('select', 'id', 'gifts_single_bankAccountId', 1);
            array_push($formFields, $choiceMade);
        } else {
            array_push($formFields, '-');
        }

        $this->fillField('gifts_single[amount]', $this->giftId + 100);
        array_push($formFields, '£' . ($this->giftId + 100) . '.00');

        // Add gifts to giftDetails array
        array_push($this->giftDetails, $formFields);
    }

    /**
     * @When I edit first gift description and amount
     */
    public function iEditGiftDescriptionAndAmount()
    {
        $formFields = [];
        $this->giftId += 1;

        $this->fillField('gifts_single[explanation]', 'random-gift-' . $this->giftId);
        array_push($formFields, 'random-gift-' . $this->giftId);
        if ($this->elementExistsOnPage('select', 'id', 'gifts_single_bankAccountId')) {
            $choiceMade = $this->iSelectBasedOnChoiceNumber('select', 'id', 'gifts_single_bankAccountId', 1);
            array_push($formFields, $choiceMade);
        } else {
            array_push($formFields, '-');
        }
        $this->fillField('gifts_single[amount]', $this->giftId + 100);
        array_push($formFields, '£' . ($this->giftId + 100) . '.00');

        // Update first gift in giftDetails array
        $this->giftDetails[0] = $formFields;
    }

    /**
     * @When I follow edit link for gifts section
     */
    public function iFollowEditLinkForGifts()
    {
        $this->iClickBasedOnAttributeTypeAndValue('a', 'id', 'edit-gifts');
    }

    /**
     * @When I follow edit link on first gift
     */
    public function iFollowEditLinkOnFirstGift()
    {
        $urlRegex = '/report\/.*\/gifts\/edit\/.*/';
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);
    }

    /**
     * @When I follow add a gift link
     */
    public function iFollowAddAGiftLink()
    {
        $urlRegex = '/report\/.*\/gifts\/add\?from\=summary$/';
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);
    }

    /**
     * @When I follow remove a gift link on first gift
     */
    public function iFollowRemoveAGiftLinkOnFirstGift()
    {
        $urlRegex = '/report\/.*\/gifts\/.*\/delete$/';
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);
        $this->removeAGift(0);
    }

    /**
     * @When I follow remove a gift link on second gift
     */
    public function iFollowRemoveAGiftLinkOnSecondGift()
    {
        $urlRegex = '/report\/.*\/gifts\/.*\/delete$/';
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 1);
        $this->removeAGift(1);
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
     * @When I confirm to remove gift
     */
    public function iChooseToRemoveGift()
    {
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'confirm_delete_confirm');
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
        $this->iFollowEditLinkOnFirstGift();
        $this->iAmOnGiftsEditPage();
        $this->iEditGiftDescriptionAndAmount();
        $this->iChooseToSaveAndContinue();
    }

    /**
     * @When I remove the second gift
     */
    public function iRemoveTheSecondGift()
    {
        $this->iFollowRemoveAGiftLinkOnSecondGift();
        $this->iAmOnGiftsDeletionPage();
        $this->iChooseToRemoveGift();
        $this->iAmOnGiftsSummaryPage();
    }

    /**
     * @When I remove the first gift
     */
    public function iRemoveTheFirstGift()
    {
        $this->iFollowRemoveAGiftLinkOnFirstGift();
        $this->iAmOnGiftsDeletionPage();
        $this->iChooseToRemoveGift();
        $this->iAmOnGiftsStartPage();
    }

    /**
     * @Then I should see the expected gifts report section responses
     */
    public function iSeeExpectedGiftsSectionResponses()
    {
        $descriptionList = $this->getSession()->getPage()->find('css', 'dl');

        if (!$descriptionList) {
            $this->throwContextualException('A dl element was not found on the page');
        }

        $descriptionListEntry = $descriptionList->findAll('css', 'dd');

        if (!$descriptionListEntry) {
            $this->throwContextualException('A dd element was not found on the page');
        }

        foreach ($descriptionListEntry as $entry) {
            if ($entry->getAttribute('class') === 'govuk-summary-list__value') {
                $actualResponse = trim(strtolower($entry->getHtml()));
            }
        }

        $summaryGifts = [];

        if (count($this->giftDetails) > 0) {
            $expectedResponse = 'yes';

            $tableBody = $this->getSession()->getPage()->find('css', 'tbody');

            if (!$tableBody) {
                $this->throwContextualException('A tbody element was not found on the page');
            }

            $tableRows = $tableBody->findAll('css', 'tr');

            if (!$tableRows) {
                $this->throwContextualException('A tr element was not found on the page');
            }

            foreach ($tableRows as $tableRow) {
                $giftFields = [];
                $tableHeader = $tableRow->find('css', 'th');

                array_push($giftFields, trim(strtolower($tableHeader->getHtml())));
                $tableFields = $tableRow->findAll('css', 'td');

                foreach ($tableFields as $tableField) {
                    array_push($giftFields, trim(strtolower($tableField->getHtml())));
                }
                array_push($summaryGifts, $giftFields);
            }
        } else {
            $expectedResponse = 'no';
        }

        assert(
            $expectedResponse == $actualResponse,
            $this->formatAssertResponse($expectedResponse, $actualResponse, 'Gift user answers', $this->getCurrentUrl())
        );

        foreach ($this->giftDetails as $key=>$gift) {
            $summaryGift = $summaryGifts[$key];
            foreach ($gift as $fkey=>$giftField) {
                assert(
                    trim(strtolower($giftField)) == trim(strtolower($summaryGift[$fkey])),
                    $this->formatAssertResponse($giftField, $summaryGift[$fkey], 'Gift names', $this->getCurrentUrl())
                );
            }
        }
    }

    public function removeAGift(int $giftNumber)
    {
        unset($this->giftDetails[$giftNumber]);
        $this->giftDetails = array_values($this->giftDetails);
    }
}
