<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Registration;

use App\Tests\Behat\BehatException;
use DateTime;

trait RegistrationTrait
{
    private array $csvRows = [];
    private string $uploadedUserEmail = '';
    private ?DateTime $registrationReportStartDate = null;
    private ?DateTime $registrationReportEndDate = null;

    /**
     * @Given /^I upload a lay csv that contains a row with \'([^\']*)\' deputy email \'([^\']*)\'$/
     */
    public function iUploadALayCsvThatContainsARowWithDeputyEmail(string $deputyType, string $email)
    {
        $this->iNavigateToAdminUploadUsersPage();

        $this->uploadedUserEmail = $email;

        switch ($deputyType) {
            case 'lay':
                $filename = 'casrec-csvs/lay-end-to-end.csv';
                $this->selectOption('form[type]', 'lay');
                $this->pressButton('Continue');
                break;
            default:
                throw new BehatException('This step only supports deputyType as lay|professional|public authority. Either add more cases or use an available deputy type.');
        }

        $this->attachFileToField('admin_upload[file]', $filename);
        $this->pressButton('Upload Lay users');
        $this->waitForAjaxAndRefresh();

        $this->csvRows = $this->transformCsvRowsToArray($filename);
    }

    /**
     * @When the deputy included in the CSV self registers and sets report period to :startDateString - :endDateString
     */
    public function theseDeputyWithEmailSelfRegisters(string $startDateString, string $endDateString)
    {
        $this->registrationReportStartDate = new DateTime($startDateString);
        $this->registrationReportEndDate = new DateTime($endDateString);

        $email = $this->uploadedUserEmail;

        $csvRow = array_filter($this->csvRows, function ($row) use ($email) {
            return $row['Email'] === $email;
        })[0];

        $explodedStartDate = explode('/', $startDateString);
        $explodedEndDate = explode('/', $endDateString);
        $explodedMadeDate = explode('-', (new DateTime($csvRow['Made Date']))->format('j-m-Y'));

        $this->visit('/register');
        $this->fillField('self_registration_firstname', $csvRow['Dep Forename']);
        $this->fillField('self_registration_lastname', $csvRow['Dep Surname']);
        $this->fillField('self_registration_email_first', $csvRow['Email']);
        $this->fillField('self_registration_email_second', $csvRow['Email']);
        $this->fillField('self_registration_postcode', $csvRow['Dep Postcode']);
        $this->fillField('self_registration_clientFirstname', $this->faker->firstName);
        $this->fillField('self_registration_clientLastname', $csvRow['Surname']);
        $this->fillField('self_registration_caseNumber', $csvRow['Case']);
        $this->pressButton('self_registration_save');

        $this->openActivationOrPasswordResetPage('', 'activation', $email);
        $this->fillField('set_password_password_first', 'DigidepsPass1234');
        $this->fillField('set_password_password_second', 'DigidepsPass1234');
        $this->checkOption('set_password_showTermsAndConditions');
        $this->pressButton('set_password_save');

        $this->assertPageContainsText('Sign in to your new account');
        $this->fillField('login_email', $email);
        $this->fillField('login_password', 'DigidepsPass1234');
        $this->pressButton('login_login');

        $this->fillField('user_details_address1', '102 Petty France');
        $this->fillField('user_details_address2', 'MOJ');
        $this->fillField('user_details_address3', 'London');
        $this->fillField('user_details_addressCountry', 'GB');
        $this->fillField('user_details_phoneMain', '01789 321234');
        $this->pressButton('user_details_save');

        var_dump($explodedMadeDate);

        $this->fillField('client_address', '1 South Parade');
        $this->fillField('client_address2', 'First Floor');
        $this->fillField('client_county', 'Notts');
        $this->fillField('client_postcode', 'NG1 2HT');
        $this->fillField('client_country', 'GB');
        $this->fillField('client_phone', '01789432876');
        $this->fillField('client_courtDate_day', $explodedMadeDate[0]);
        $this->fillField('client_courtDate_month', $explodedMadeDate[1]);
        $this->fillField('client_courtDate_year', $explodedMadeDate[2]);
        $this->pressButton('client_save');

        $this->fillField('report_startDate_day', $explodedStartDate[0]);
        $this->fillField('report_startDate_month', $explodedStartDate[1]);
        $this->fillField('report_startDate_year', $explodedStartDate[2]);
        $this->fillField('report_endDate_day', $explodedEndDate[0]);
        $this->fillField('report_endDate_month', $explodedEndDate[1]);
        $this->fillField('report_endDate_year', $explodedEndDate[2]);
        $this->pressButton('report_save');
    }
}
