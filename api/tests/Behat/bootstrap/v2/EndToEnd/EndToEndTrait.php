<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\EndToEnd;

use App\Entity\User;
use App\Tests\Behat\BehatException;

trait EndToEndTrait
{
    private array $csvRows = [];

    /**
     * @Given /^I fill in the accounts section$/
     */
    public function iFillInTheAccountsSection()
    {
        $this->fillInAccountsSection();
        $this->submittedAnswersByFormSections = [];
    }

    /**
     * @Given /^I fill in the actions section$/
     */
    public function iFillInTheActionsSection()
    {
        $this->fillInActionsSection();
        $this->submittedAnswersByFormSections = [];
    }

    /**
     * @Given /^I fill in the additional information section$/
     */
    public function iFillInTheAdditionalInformationSection()
    {
        $this->fillInAdditionalInfoSection();
        $this->submittedAnswersByFormSections = [];
    }

    /**
     * @Given /^I fill in the assets section$/
     */
    public function iFillInTheAssetsSection()
    {
        $this->fillInAssetsSection();
        $this->submittedAnswersByFormSections = [];
    }

    /**
     * @Given /^I fill in the client benefits check section$/
     */
    public function iFillInTheClientBenefitsCheckSection()
    {
        $this->fillInClientBenefitsCheckSection();
        $this->submittedAnswersByFormSections = [];
    }

    /**
     * @Given /^I fill in the contacts section$/
     */
    public function iFillInTheContactsSection()
    {
        $this->fillInContactsSections();
        $this->submittedAnswersByFormSections = [];
    }

    /**
     * @Given /^I fill in the debts section$/
     */
    public function iFillInTheDebtsSection()
    {
        $this->fillInDebtsSection();
        $this->submittedAnswersByFormSections = [];
    }

    /**
     * @Given /^I fill in the decisions section$/
     */
    public function iFillInTheDecisionsSection()
    {
        $this->fillInDecisionsSection();
        $this->submittedAnswersByFormSections = [];
    }

    /**
     * @Given /^I fill in the documents section$/
     */
    public function iFillInTheDocumentsSection()
    {
        $this->fillInDocumentsSection();
        $this->submittedAnswersByFormSections = [];
    }

    /**
     * @Given /^I fill in the gifts section$/
     */
    public function iFillInTheGiftsSection()
    {
        $this->fillInGiftsSection();
        $this->submittedAnswersByFormSections = [];
    }

    /**
     * @Given /^I fill in the health and lifestyle section$/
     */
    public function iFillInTheHealthAndLifestyleSection()
    {
        $this->fillInHealthAndLifeStyle();
        $this->submittedAnswersByFormSections = [];
    }

    /**
     * @Given /^I fill in the money in high assets section$/
     */
    public function iFillInTheMoneyInHighAssetsSection()
    {
        $this->fillInMoneyInHighAssetsSection();
        $this->submittedAnswersByFormSections = [];
    }

    /**
     * @Given /^I fill in the money out section$/
     */
    public function iFillInTheMoneyOutSection()
    {
        $this->fillInMoneyOutSection();
        $this->submittedAnswersByFormSections = [];
    }

    /**
     * @Given /^I fill in the visits and care section$/
     */
    public function iFillInTheVisitsAndCareSection()
    {
        $this->fillInVisitsCareSection();
        $this->submittedAnswersByFormSections = [];
    }

    /**
     * @Then /^I should see next years report details$/
     */
    public function iShouldSeeNextYearsReportDetails()
    {
        $nextYearStartDate = (clone $this->registrationReportStartDate)->modify('+1 year')->format('Y m d');
        $nextYearEndDate = (clone $this->registrationReportEndDate)->modify('+1 year')->format('Y m d');

        if (!str_contains($this->getSession()->getPage()->getContent(), $nextYearStartDate)) {
            $message = sprintf('Next years expected start date (%s) not visible on page', $nextYearStartDate);
            throw new BehatException($message);
        }

        if (!str_contains($this->getSession()->getPage()->getContent(), $nextYearEndDate->format('Y m d'))) {
            $message = sprintf('Next years expected end date (%s) not visible on page', $nextYearEndDate);
            throw new BehatException($message);
        }
    }

    /**
     * @When /^I open the (admin |)(activation|password reset) page for "(.+)"$/
     */
    public function openActivationOrPasswordResetPage($admin, $pageType, $email)
    {
        $token = $this->em->getRepository(User::class)->findOneBy(['email' => $this->uploadedUserEmail])->getRegistrationToken();
        $this->visitAdminPath('/logout');

        $page = 'activation' === $pageType ? 'activate' : 'password-reset';

        if ('' === $admin || false === $admin) {
            $this->visitPath("/user/$page/$token");
        } else {
            $this->visitAdminPath("/user/$page/$token");
        }
    }
}
