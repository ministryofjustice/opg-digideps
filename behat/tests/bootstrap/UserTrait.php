<?php

namespace DigidepsBehat;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Session;
use Symfony\Component\HttpFoundation\Response;

trait UserTrait
{
    // added here for simplicity
    private static $roleStringToRoleName = [
        'super admin' => 'ROLE_SUPER_ADMIN',
        'admin' => 'ROLE_ADMIN',
        'lay deputy' => 'ROLE_LAY_DEPUTY',
        'ad' => 'ROLE_AD',
        'pa named' => 'ROLE_PA_NAMED',
        'prof named' => 'ROLE_PROF_NAMED'
    ];

    /**
     * Requires an authenticated admin user to use in a scenario
     *
     * @Given the following admins exist:
     */
    public function adminsExist(TableNode $table)
    {
        foreach ($table as $inputs) {
            $this->assertValidInputs($inputs);

            $query = http_build_query($inputs);

            $this->visitAdminPath("/admin/fixtures/createAdmin?$query");
        }
    }

    private function assertValidInputs(array $inputs): void
    {
        $adminType = $inputs['adminType'];

        if (!in_array($adminType, ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])) {
            throw new \Exception("adminType should be 'ROLE_ADMIN' or 'ROLE_SUPER_ADMIN'; '$adminType' provided");
        }

        foreach(['adminType', 'firstName', 'lastName', 'email', 'activated'] as $key) {
            $missingKeys = [];

            if (!array_key_exists($key, $inputs)) {
                $missingKeys[] = $key;
            }

            if (count($missingKeys) > 0) {
                $missingKeysString = implode($missingKeys, ', ');
                throw new \Exception("Missing required parameter headings: $missingKeysString");
            }
        }
    }

    /**
     * it's assumed you are logged as an admin and you are on the admin homepage (with add user form).
     *
     * @Given the following users exist:
     */
    public function usersExist(TableNode $table)
    {
        foreach ($table as $inputs) {
            $this->assertValidRole($inputs['deputyType']);

            $ndr = $inputs['ndr'];
            $deputyType = $inputs['deputyType'];
            $firstName = $inputs['firstName'];
            $lastName = $inputs['lastName'];
            $email = $inputs['email'];
            $postCode = $inputs['postCode'];
            $activated = $inputs['activated'];

            $query = "ndr=$ndr&deputyType=$deputyType&firstName=$firstName&lastName=$lastName&email=$email&postCode=$postCode&activated=$activated";

            $this->visitAdminPath("/admin/fixtures/createUser?$query");
        }
    }

    private function assertValidRole(string $roleName): void
    {
        if (!in_array($roleName, ['ADMIN', 'AD', 'LAY', 'PA', 'PROF'])) {
            throw new \Exception("DeputyType should be one of 'ADMIN', 'AD', 'LAY', 'PA', 'PROF'; '$roleName' provided");
        }
    }

    /**
     * it's assumed you are logged as an admin and you are on the admin homepage (with add user form).
     *
     * @When I create a new :ndrType :role user :firstname :lastname with email :email and postcode :postcode
     */
    public function iCreateTheUserWithEmailAndPostcode($ndrType, $role, $firstname, $lastname, $email, $postcode = '')
    {
        $this->clickLink('Add new user');
        $this->fillField('admin_email', $email);
        $this->fillField('admin_firstname', $firstname);
        $this->fillField('admin_lastname', $lastname);
        if (!empty($postcode)) {
            $this->fillField('admin_addressPostcode', $postcode);
        }
        $roleName = self::$roleStringToRoleName[strtolower($role)];

        if ($roleName === 'ROLE_LAY_DEPUTY' || $roleName === 'ROLE_PA_NAMED' || $roleName === 'ROLE_PROF_NAMED') {
            $this->fillField('admin_roleType_0', 'deputy');
            $this->fillField('admin_roleNameDeputy', $roleName);
            switch ($ndrType) {
                case 'NDR-enabled':
                    $this->checkOption('admin_ndrEnabled');
                    break;
                case 'NDR-disabled':
                    $this->uncheckOption('admin_ndrEnabled');
                    break;
                default:
                    throw new \RuntimeException("$ndrType not a valid NDR type");
            }
        } else {
            $this->fillField('admin_roleType_1', 'staff');
            $this->fillField('admin_roleNameStaff', $roleName);
        }

        $this->clickOnBehatLink('save');
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
    }

    /**
     * @Given I change the user :userId token to :token dated last week
     */
    public function iChangeTheUserToken($userId, $token)
    {
        $tokenDate = (new \DateTime('-7days'))->format('Y-m-d');
        $query = sprintf('UPDATE dd_user SET registration_token = \'%s\', token_date = \'%s\' WHERE email = \'%s\'', $token, $tokenDate, $userId);
        $command = sprintf('psql %s -c "%s"', self::$dbName, $query);

        exec($command);
    }

    /**
     * @When I activate the user :email with password :password
     */
    public function iActivateTheUserAndSetThePasswordTo($email, $password)
    {
        $this->visit('/logout');
        $this->openActivationOrPasswordResetPage(false, 'activation', $email);
        $this->assertResponseStatus(200);
        $this->fillField('set_password_password_first', $password);
        $this->fillField('set_password_password_second', $password);
        $this->checkOption('set_password_showTermsAndConditions');
        $this->pressButton('set_password_save');
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
    }

    /**
     * @When I activate the admin user :email with password :password
     */
    public function iActivateTheAdminUserAndSetThePasswordTo($email, $password)
    {
        $this->visitAdminPath('/logout');
        $this->openActivationOrPasswordResetPage(true, 'activation', $email);
        $this->assertResponseStatus(200);
        $this->fillField('set_password_password_first', $password);
        $this->fillField('set_password_password_second', $password);
        $this->pressButton('set_password_save');
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
    }

    /**
     * @When I activate the named deputy :email with password :password
     */
    public function iActivateTheNamedDeputyAndSetThePasswordTo($email, $password)
    {
        $this->visit('/logout');
        $this->openActivationOrPasswordResetPage(false, 'activation', $email);
        $this->assertResponseStatus(200);
        $this->checkOption('agree_terms_agreeTermsUse');
        $this->pressButton('agree_terms_save');
        $this->fillField('set_password_password_first', $password);
        $this->fillField('set_password_password_second', $password);
        $this->checkOption('set_password_showTermsAndConditions');
        $this->pressButton('set_password_save');
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
        $this->fillField('login_email', $email);
        $this->fillField('login_password', $password);
        $this->pressButton('login_login');
        $this->fillField('user_details_jobTitle', 'Main org contact');
        $this->pressButton('user_details_save');
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
    }

    /**
     * @When I fill in the password fields with :password
     */
    public function iFillThePasswordFieldsWith($password)
    {
        $this->fillField('set_password_password_first', $password);
        $this->fillField('set_password_password_second', $password);
    }

    /**
     * @When I set the user details to:
     */
    public function iSetTheUserDetailsTo(TableNode $table)
    {
        $this->visit('/user/details');
        $rows = $table->getRowsHash();
        if (isset($rows['name'])) {
            if (trim($rows['name'][0]) != 'PREFILLED') {
                $this->fillField('user_details_firstname', $rows['name'][0]);
            }
            if (trim($rows['name'][1]) != 'PREFILLED') {
                $this->fillField('user_details_lastname', $rows['name'][1]);
            }
        }

        if (isset($rows['address'])) {
            $this->fillField('user_details_address1', $rows['address'][0]);
            $this->fillField('user_details_address2', $rows['address'][1]);
            $this->fillField('user_details_address3', $rows['address'][2]);
            if (trim($rows['address'][3]) != 'PREFILLED') {
                $this->fillField('user_details_addressPostcode', $rows['address'][3]);
            }
            $this->fillField('user_details_addressCountry', $rows['address'][4]);
        }

        if (isset($rows['phone'])) {
            $this->fillField('user_details_phoneMain', $rows['phone'][0]);
            $this->fillField('user_details_phoneAlternative', $rows['phone'][1]);
        }

        $this->pressButton('user_details_save');
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
    }

    /**
     * @When I set the client details to:
     */
    public function iSetTheClientDetailsTo(TableNode $table)
    {
        $this->visit('/client/add');
        $rows = $table->getRowsHash();
        if (isset($rows['name'])) {
            if (trim($rows['name'][0]) != 'PREFILLED') {
                $this->fillField('client_firstname', $rows['name'][0]);
            }
            if (trim($rows['name'][1]) != 'PREFILLED') {
                $this->fillField('client_lastname', $rows['name'][1]);
            }
        }
        if (array_key_exists('caseNumber', $rows)) {
            $this->fillField('client_caseNumber', $rows['caseNumber'][0]);
        }
        $this->fillField('client_courtDate_day', $rows['courtDate'][0]);
        $this->fillField('client_courtDate_month', $rows['courtDate'][1]);
        $this->fillField('client_courtDate_year', $rows['courtDate'][2]);
        $this->fillField('client_address', $rows['address'][0]);
        $this->fillField('client_address2', $rows['address'][1]);
        $this->fillField('client_county', $rows['address'][2]);
        if (trim($rows['address'][3]) != 'PREFILLED') {
            $this->fillField('client_postcode', $rows['address'][3]);
        }
        $this->fillField('client_country', $rows['address'][4]);
        $this->fillField('client_phone', $rows['phone'][0]);

        $this->pressButton('client_save');
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
    }

    /**
     * @When I set the client details with:
     */
    public function iSetTheClientDetailsWith(TableNode $table)
    {
        $this->visit('/client/add');
        $rows = $table->getRowsHash();
        if (isset($rows['name'])) {
            if ($rows['name'][0] !== 'PREFILLED') {
                $this->fillField('client_firstname', $rows['name'][0]);
            }
            if (isset($rows['name']) && $rows['name'][1] !== 'PREFILLED') {
                $this->fillField('client_lastname', $rows['name'][1]);
            }
        }
        $this->fillField('client_firstname', $rows['name'][0]);
        $this->fillField('client_lastname', $rows['name'][1]);
        $this->fillField('client_caseNumber', $rows['caseNumber'][0]);
        $this->fillField('client_courtDate_day', $rows['courtDate'][0]);
        $this->fillField('client_courtDate_month', $rows['courtDate'][1]);
        $this->fillField('client_courtDate_year', $rows['courtDate'][2]);
        $this->fillField('client_address', $rows['address'][0]);
        $this->fillField('client_address2', $rows['address'][1]);
        $this->fillField('client_county', $rows['address'][2]);
        if ($rows['address'][3] !== 'PREFILLED') {
            $this->fillField('client_postcode', $rows['address'][3]);
        }
        $this->fillField('client_country', $rows['address'][4]);
        $this->fillField('client_phone', $rows['phone'][0]);
    }

    /**
     * @Given I truncate the users from CASREC:
     */
    public function iTruncateTheUsersFromCasrec()
    {
        $query = 'TRUNCATE TABLE casrec';
        $command = sprintf('psql %s -c "%s"', self::$dbName, $query);

        exec($command);
    }

    /**
     * @Given I add the following users to CASREC:
     */
    public function iAddTheFollowingUsersToCASREC(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $row = array_map([$this, 'casRecNormaliseValue'], $row);
            $this->dbQueryRaw('casrec', [
                'client_case_number' => $row['Case'],
                'client_lastname'    => $row['Surname'],
                'deputy_no'          => $row['Deputy No'],
                'deputy_lastname'    => $row['Dep Surname'],
                'deputy_postcode'    => $row['Dep Postcode'],
                'type_of_report'     => $row['Typeofrep'],
                'other_columns'      => str_replace('"', '\\"',serialize($row)),
                'order_date'         => (new \DateTime($row['OrderDate']))->format('Y-m-d H:i:s'),
                'corref'             => $row['Corref']
            ]);
        }
    }

    public static function casRecNormaliseValue($value)
    {
        $value = trim($value);
        $value = strtolower($value);
        // remove MBE suffix
        $value = preg_replace('/ (mbe|m b e)$/i', '', $value);
        // remove characters that are not a-z or 0-9 or spaces
        $value = preg_replace('/([^a-z0-9])/i', '', $value);

        return $value;
    }

    /**
     * @Given there is an activated :depType user with NDR :ndrStatus and email :email and password :password
     */
    public function iCreateAndActivateUser($depType, $ndrStatus, $email, $password)
    {
        $ndrStatus = 'NDR-' . $ndrStatus;
        $this->iCreateTheUserWithEmailAndPostcode($ndrStatus, $depType, 'Lay', 'Dep', $email, 'SW11AA');
        $this->iActivateTheUserAndSetThePasswordTo($email, $password);
        $this->iLogInWithNewPassword($email, $password);

        $this->iAddTheFollowingUsersToCASREC(new TableNode([
            ['Case', 'Surname', 'Deputy No', 'Dep Surname', 'Dep Postcode', 'Typeofrep', 'OrderDate', 'Corref'],
            ['12355555', 'Jones', '00213', 'Dep', 'SW11AA', 'OPG102','2011-04-30', 'l2']
        ]));

        $this->iSetTheUserDetailsTo(new TableNode([
            ['address', '16 Deputy Road', 'Beeston', 'Notts', 'PREFILLED', 'GB'],
            ['phone', '07987123123', '', '', '', '']
        ]));

        $this->iSetTheClientDetailsTo(new TableNode([
            ['name', 'Client', 'Jones', '', '', ''],
            ['caseNumber', '12355555', '', '', '', ''],
            ['address', '16 Client Road', 'Beeston', 'Notts', 'NG12LK', 'GB'],
            ['courtDate', 1, 11, 2018, '', ''],
            ['phone', '07987123122', '', '', '', '']
        ]));

        $this->iSetTheReportStartDateToAndEndDateTo('1/11/2018');
        $this->iSetTheReportEndDateToAndEndDateTo('1/10/2019');
    }

    /**
     * @Given I :action NDR for user :email
     */
    public function iEnableNdrForUser($action, $email)
    {
        $this->clickOnBehatLink('user-' . $email);
        $this->clickLink('Edit user');

        strtolower($action) === 'enable' ?
            $this->checkOption('admin_ndrEnabled') :
            $this->uncheckOption('admin_ndrEnabled');

        $this->clickOnBehatLink('save');
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
    }

    /**
     * @param $email
     * @param $password
     */
    private function iLogInWithNewPassword($email, $password): void
    {
        $this->assertPageContainsText('Sign in to your new account');
        $this->fillField('login_email', $email);
        $this->fillField('login_password', $password);
        $this->pressButton('login_login');
    }
}
