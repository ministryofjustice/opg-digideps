<?php

namespace DigidepsBehat;

use Behat\Gherkin\Node\TableNode;

trait UserTrait
{
    // added here for simplicity
    private static $roleNameToRoleId = ['admin' => 1, 'lay deputy' => 2, 'ad' => 5];

    /**
     * it's assumed you are logged as an admin and you are on the admin homepage (with add user form).
     * 
     * @When I create a new :odrType :role user :firstname :lastname with email :email
     */
    public function iCreateTheUserWithEmail($odrType, $role, $firstname, $lastname, $email)
    {
        $this->fillField('admin_email', $email);
        $this->fillField('admin_firstname', $firstname);
        $this->fillField('admin_lastname', $lastname);
        $roleId = self::$roleNameToRoleId[strtolower($role)];
        $this->fillField('admin_roleId', $roleId);
        switch($odrType) {
            case 'ODR-enabled':
                $this->checkOption('admin_odrEnabled');
                break;
            case 'ODR-disabled':
                $this->uncheckOption('admin_odrEnabled');
                break;
            default:
                throw new \RuntimeException("$odrType not a valid ODR type");
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
        $this->visitBehatLink("user/{$userId}/token/{$token}/token-date/-7days");
        $this->assertResponseStatus(200);
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
        $this->pressButton('set_password_save');
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
    }

    /**
     * @When I set the user details to:
     */
    public function iSetTheUserDetailsTo(TableNode $table)
    {
        $this->visit('/user/details');
        $rows = $table->getRowsHash();

        $this->fillField('user_details_firstname', $rows['name'][0]);
        $this->fillField('user_details_lastname', $rows['name'][1]);

        if (isset($rows['address'])) {
            $this->fillField('user_details_address1', $rows['address'][0]);
            $this->fillField('user_details_address2', $rows['address'][1]);
            $this->fillField('user_details_address3', $rows['address'][2]);
            $this->fillField('user_details_addressPostcode', $rows['address'][3]);
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
        $this->fillField('client_firstname', $rows['name'][0]);
        $this->fillField('client_lastname', $rows['name'][1]);
        $this->fillField('client_caseNumber', $rows['caseNumber'][0]);
        $this->fillField('client_courtDate_day', $rows['courtDate'][0]);
        $this->fillField('client_courtDate_month', $rows['courtDate'][1]);
        $this->fillField('client_courtDate_year', $rows['courtDate'][2]);
        if (isset($rows['allowedCourtOrderTypes_0'][0])) {
            $this->fillField('client_allowedCourtOrderTypes_0', $rows['allowedCourtOrderTypes_0'][0]);
        }
        if (isset($rows['allowedCourtOrderTypes_1'][0])) {
            $this->fillField('client_allowedCourtOrderTypes_1', $rows['allowedCourtOrderTypes_1'][0]);
        }
        $this->fillField('client_address', $rows['address'][0]);
        $this->fillField('client_address2', $rows['address'][1]);
        $this->fillField('client_county', $rows['address'][2]);
        $this->fillField('client_postcode', $rows['address'][3]);
        $this->fillField('client_country', $rows['address'][4]);
        $this->fillField('client_phone', $rows['phone'][0]);

        $this->pressButton('client_save');
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
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
            $query = sprintf('INSERT INTO casrec'.
                '(client_case_number, client_lastname, deputy_no, deputy_lastname, deputy_postcode)'.
                " VALUES('%s','%s','%s','%s','%s')",
                $row['Case'], $row['Surname'], $row['Deputy No'], $row['Dep Surname'], $row['Dep Postcode']
            );

            $command = sprintf('psql %s -c "%s"', self::$dbName, $query);

            exec($command);
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
}
