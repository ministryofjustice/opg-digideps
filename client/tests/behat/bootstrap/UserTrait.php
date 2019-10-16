<?php

namespace DigidepsBehat;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use Behat\Gherkin\Node\TableNode;

trait UserTrait
{
    // added here for simplicity
    private static $roleStringToRoleName = [
        'admin' => User::ROLE_ADMIN,
        'lay deputy' => User::ROLE_LAY_DEPUTY,
        'ad' => User::ROLE_AD,
        'case manager' => User::ROLE_CASE_MANAGER,
    ];

    /**
     * it's assumed you are logged as an admin and you are on the admin homepage (with add user form).
     *
     * @When I create a new :ndrType :role user :firstname :lastname with email :email and postcode :postcode
     */
    public function iCreateTheUserWithEmailAndPostcode($ndrType, $role, $firstname, $lastname, $email, $postcode = '')
    {
        $this->clickOnBehatLink('user-add-new');
        $this->fillField('admin_email', $email);
        $this->fillField('admin_firstname', $firstname);
        $this->fillField('admin_lastname', $lastname);
        if (!empty($postcode)) {
            $this->fillField('admin_addressPostcode', $postcode);
        }
        $roleName = self::$roleStringToRoleName[strtolower($role)];

        if ($roleName === User::ROLE_LAY_DEPUTY) {
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
     * @When I activate the user with password :password
     */
    public function iActivateTheUserAndSetThePasswordTo($password)
    {
        $this->visit('/logout');
        $this->iOpenTheSpecificLinkOnTheEmail('/user/activate/');
        $this->assertResponseStatus(200);
        $this->fillField('set_password_password_first', $password);
        $this->fillField('set_password_password_second', $password);
        $this->checkOption('set_password_showTermsAndConditions');
        $this->pressButton('set_password_save');
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
    }

    /**
     * @TODO to use in places where needed
     * @When I activate the user with password :password - no T&C expected
     */
    public function iActivateTheUserAndSetThePasswordToNoTcExpected($password)
    {
        $this->visit('/logout');
        $this->iOpenTheSpecificLinkOnTheEmail('/user/activate/');
        $this->assertResponseStatus(200);
        $this->fillField('set_password_password_first', $password);
        $this->fillField('set_password_password_second', $password);
        $this->pressButton('set_password_save');
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
     * @Then There should be a lay deputy account with id :userid awaiting activation
     */
    public function thereShouldBeAwaitingActivation($userid)
    {
        throw new PendingException();
        // Login to admin
        // The Find the line that has this user
        // confirm the type is lay deputy
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
                'other_columns'      => str_replace('"', '\\"',serialize($row))
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
        $this->iActivateTheUserAndSetThePasswordTo($password);

        $this->iAddTheFollowingUsersToCASREC(new TableNode([
            ['Case', 'Surname', 'Deputy No', 'Dep Surname', 'Dep Postcode', 'Typeofrep'],
            ['12355555', 'Jones', '00213', 'Dep', 'SW11AA', 'OPG102']
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

        strtolower($action) === 'enable' ?
            $this->checkOption('admin_ndrEnabled') :
            $this->uncheckOption('admin_ndrEnabled');

        $this->clickOnBehatLink('save');
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
    }
}
