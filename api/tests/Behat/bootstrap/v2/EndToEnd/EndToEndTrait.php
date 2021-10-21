<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\EndToEnd;

trait EndToEndTrait
{
    private string $uploadedUserEmail = '';

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
     * @Given /^I upload a lay csv that contains a row with deputy email \'([^\']*)\'$/
     */
    public function iUploadALayCsvThatContainsARowWithDeputyEmail(string $email)
    {
        $this->iNavigateToAdminUploadUsersPage();

        $this->uploadedUserEmail = $email;

        $this->attachFileToField('admin_upload[file]', 'add/path/for/csv.file');
        $this->pressButton('Upload Lay users');
        $this->waitForAjaxAndRefresh();
    }
}
