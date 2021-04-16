Feature: A deputy user edits their details
  As a deputy user
  So I can keep my account up to date
  I need to be able to update my user details

  Scenario: Creating users to edit
    Given I am logged in to admin as "super-admin@publicguardian.gov.uk" with password "DigidepsPass1234"

    Given the following users exist:
      | ndr      | deputyType | firstName | lastName       | email                 | postCode | activated |
      | disabled | LAY        | Winifred  | Sanderson      | w.sanderson@test.com  | HA4      | true      |
      | disabled | PROF       | Sarah     | Sanderson      | s.sanderson@test.com  | HA4      | true      |
      | disabled | PA         | Mary      | Sanderson      | m.sanderson@test.com  | HA4      | true      |
      | disabled | LAY        | Billy     | Butcherson     | b.butcherson@test.com | HA4      | true      |

  Scenario: A lay deputy edits their details
    Given I am logged in as "w.sanderson@test.com" with password "DigidepsPass1234"
    And I view the lay deputy edit your details page
    And the following fields should have the corresponding values:
      | profile_firstname        | Winifred             |
      | profile_lastname         | Sanderson            |
      | profile_address1         | Victoria road        |
      | profile_address2         |                      |
      | profile_address3         |                      |
      | profile_addressPostcode  | HA4                  |
      | profile_addressCountry   | GB                   |
      | profile_phoneMain        | 07911111111111       |
      | profile_phoneAlternative |                      |
      | profile_email            | w.sanderson@test.com |
    When I fill in the following:
      | profile_firstname       | |
      | profile_lastname        | |
      | profile_address1        | |
      | profile_addressPostcode | |
      | profile_addressCountry  | |
      | profile_phoneMain       | |
      | profile_email           | |
    And I press "profile_save"
    Then the following fields should have an error:
      | profile_firstname       |
      | profile_lastname        |
      | profile_address1        |
      | profile_addressPostcode |
      | profile_addressCountry  |
      | profile_phoneMain       |
      | profile_email           |
    Then the form should be invalid
    When I fill in the following:
      | profile_firstname        | Max                 |
      | profile_lastname         | Dennison            |
      | profile_address1         | 10 Salems Lane      |
      | profile_address2         | Salem               |
      | profile_address3         | Massachusetts       |
      | profile_addressPostcode  | SW1H 9AA            |
      | profile_addressCountry   | GB                  |
      | profile_phoneMain        | 020 3334 3556       |
      | profile_phoneAlternative | 020 1234 5679       |
      | profile_email            | m.dennison@test.com |
    And I press "profile_save"
    Then the form should be valid
    And the following fields should have the corresponding values:
      | profile_firstname        | Max                 |
      | profile_lastname         | Dennison            |
      | profile_address1         | 10 Salems Lane      |
      | profile_address2         | Salem               |
      | profile_address3         | Massachusetts       |
      | profile_addressPostcode  | SW1H 9AA            |
      | profile_addressCountry   | GB                  |
      | profile_phoneMain        | 020 3334 3556       |
      | profile_phoneAlternative | 020 1234 5679       |
      | profile_email            | m.dennison@test.com |

  Scenario Outline: A deputy that belongs to an Organisation edits their details
    Given I am logged in as "<email>" with password "DigidepsPass1234"
    And I view the org deputy edit your details page
    And the following fields should have the corresponding values:
      | profile_firstname        | <first_name>         |
      | profile_lastname         | <last_name>          |
      | profile_address1         | Victoria road        |
      | profile_address2         |                      |
      | profile_address3         |                      |
      | profile_addressPostcode  | HA4                  |
      | profile_addressCountry   | GB                   |
      | profile_phoneMain        | 07911111111111       |
      | profile_phoneAlternative |                      |
      | profile_email            |  <email>             |
    When I fill in the following:
      | profile_firstname       | |
      | profile_lastname        | |
      | profile_address1        | |
      | profile_addressPostcode | |
      | profile_addressCountry  | |
      | profile_phoneMain       | |
      | profile_email           | |
    And I press "profile_save"
    Then the following fields should have an error:
      | profile_firstname       |
      | profile_lastname        |
      | profile_email           |
    Then the form should be invalid
    When I fill in the following:
      | profile_firstname        | Max            |
      | profile_lastname         | Dennison       |
      | profile_address1         | 10 Salems Lane |
      | profile_address2         | Salem          |
      | profile_address3         | Massachusetts  |
      | profile_addressPostcode  | SW1H 9AA       |
      | profile_addressCountry   | GB             |
      | profile_phoneMain        | 020 3334 3556  |
      | profile_phoneAlternative | 020 1234 5679  |
      | profile_email            | <new_email>    |
    And I press "profile_save"
    Then the form should be valid
    When I am logged in as "<new_email>" with password "DigidepsPass1234"
    And I view the org deputy edit your details page
    And the following fields should have the corresponding values:
      | profile_firstname        | Max            |
      | profile_lastname         | Dennison       |
      | profile_address1         | 10 Salems Lane |
      | profile_address2         | Salem          |
      | profile_address3         | Massachusetts  |
      | profile_addressPostcode  | SW1H 9AA       |
      | profile_addressCountry   | GB             |
      | profile_phoneMain        | 020 3334 3556  |
      | profile_phoneAlternative | 020 1234 5679  |
      | profile_email            | <new_email>    |
    Examples:
      | email                | new_email       | first_name | last_name |
      | s.sanderson@test.com | t.binx@test.com | Sarah      | Sanderson |
      | m.sanderson@test.com | e.binx@test.com | Mary       | Sanderson |

  Scenario: A deputy changes their password
    Given I am logged in as "b.butcherson@test.com" with password "DigidepsPass1234"
    And I view the lay deputy change password page
        # wrong old password
    When I fill in "change_password_current_password" with "this.is.the.wrong.password"
    And I press "change_password_save"
    Then the following fields should have an error:
      | change_password_current_password |
      | change_password_password_first |
        # invalid new password
    When I fill in the following:
      | change_password_current_password | DigidepsPass1234 |
      | change_password_password_first | 1 |
      | change_password_password_second | 2 |
    And I press "change_password_save"
    Then the following fields should have an error:
      | change_password_password_first |
        # unmatching new passwords
    When I fill in the following:
      | change_password_current_password | DigidepsPass1234 |
      | change_password_password_first | DigidepsPass1234 |
      | change_password_password_second | DigidepsPass12345 |
    And I press "change_password_save"
    Then the following fields should have an error:
      | change_password_password_first |
        #empty password
    When I fill in the following:
      | change_password_current_password | DigidepsPass1234 |
      | change_password_password_first | |
      | change_password_password_second | |
    And I press "change_password_save"
    Then the following fields should have an error:
      | change_password_password_first |
      #too common password
    When I fill in the following:
      | change_password_current_password | DigidepsPass1234 |
      | change_password_password_first | Password123 |
      | change_password_password_second | Password123 |
    And I press "change_password_save"
    Then the following fields should have an error:
      | change_password_password_first |
      # valid new password
    When I fill in the following:
      | change_password_current_password | DigidepsPass1234 |
      | change_password_password_first | DigidepsPass12345 |
      | change_password_password_second | DigidepsPass12345 |
    And I press "change_password_save"
    Then the form should be valid
    And I should be on "/login"
    And I should see "Sign in with your new password"
        # restore old password (and assert the current password can be used as old password)
    When I am logged in as "b.butcherson@test.com" with password "DigidepsPass12345"
    Then the response status code should be 200
