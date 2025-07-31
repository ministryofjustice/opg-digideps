<?php

namespace App\Tests\Behat\Registration;

use App\Tests\Behat\Common\BaseFeatureContext;
use App\Tests\Behat\Common\LinksTrait;
use Behat\Gherkin\Node\TableNode;

class RegistrationFeatureContext extends BaseFeatureContext
{
    use LinksTrait;

    /**
     * @Given the self registration lookup table is empty
     */
    public function theSelfRegistrationLookupTableIsEmpty()
    {
        $query = 'DELETE FROM pre_registration';
        $command = sprintf('psql %s -c "%s"', self::$dbName, $query);
        exec($command);
    }

    /**
     * @Given an admin user uploads the :file file into the Lay CSV uploader
     */
    public function anAdminUserUploadsTheFileIntoTheLayCsvUploader($file)
    {
        $this->iAmLoggedInToAdminAsWithPassword('admin@publicguardian.gov.uk', 'DigidepsPass1234');
        $this->visitAdminPath('/admin/pre-registration-upload');
        $this->attachFileToField('admin_upload_file', $file);
        $this->pressButton('admin_upload_upload');
    }

    /**
     * @When these deputies register to deputise the following court orders:
     */
    public function theseDeputiesRegisterToDeputiseTheFollowingCourtOrders(TableNode $table)
    {
        foreach ($table as $courtOrder) {
            $this->visit('/register');
            $this->fillField('self_registration_firstname', 'Brian');
            $this->fillField('self_registration_lastname', $courtOrder['deputySurname']);
            $this->fillField('self_registration_email_first', $courtOrder['deputyEmail']);
            $this->fillField('self_registration_email_second', $courtOrder['deputyEmail']);
            $this->fillField('self_registration_postcode', $courtOrder['deputyPostCode']);
            $this->fillField('self_registration_clientFirstname', 'Billy');
            $this->fillField('self_registration_clientLastname', $courtOrder['clientSurname']);
            $this->fillField('self_registration_caseNumber', $courtOrder['caseNumber']);
            $this->pressButton('self_registration_save');

            $this->openActivationOrPasswordResetPage('', 'activation', $courtOrder['deputyEmail']);
            $this->fillField('set_password_password_first', 'DigidepsPass1234');
            $this->fillField('set_password_password_second', 'DigidepsPass1234');
            $this->checkOption('set_password_showTermsAndConditions');
            $this->pressButton('set_password_save');

            $this->assertPageContainsText('Sign in to your new account');
            $this->fillField('login_email', $courtOrder['deputyEmail']);
            $this->fillField('login_password', 'DigidepsPass1234');
            $this->pressButton('login_login');

            $this->fillField('user_details_address1', '102 Petty France');
            $this->fillField('user_details_address2', 'MOJ');
            $this->fillField('user_details_address3', 'London');
            $this->fillField('user_details_addressCountry', 'GB');
            $this->fillField('user_details_phoneMain', '01789 321234');
            $this->pressButton('user_details_save');

            $this->fillField('client_address', '1 South Parade');
            $this->fillField('client_address2', 'First Floor');
            $this->fillField('client_county', 'Notts');
            $this->fillField('client_postcode', 'NG1 2HT');
            $this->fillField('client_country', 'GB');
            $this->fillField('client_phone', '01789432876');
            $this->fillField('client_courtDate_day', '01');
            $this->fillField('client_courtDate_month', '01');
            $this->fillField('client_courtDate_year', '2016');
            $this->pressButton('client_save');
        }
    }
}
