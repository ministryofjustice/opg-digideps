Feature: Organisation admins can edit members of their organisation

  Scenario: Set up organisation and users
    Given the following organisations exist:
      | name               | emailIdentifier              | activated |
      | Malvern Solicitors | @malvern.example             | true      |
      | Rey Gusciora       | guscioraonline@gmail.example | true      |
    And the following users exist:
      | ndr      | deputyType | firstName | lastName | email                        | postCode | activated |
      | disabled | PROF       | Lavern    | Degroot  | l.degroot@malvern.example    | SW1H 9AJ | true      |
      | disabled | PROF       | Landon    | Swayzer  | l.swayzer@outsource.example  | SW1H 9AJ | true      |
      | disabled | PROF       | Rey       | Gusciora | guscioraonline@gmail.example | SW1H 9AJ | true      |
    And the following users are in the organisations:
      | userEmail                    | orgName            |
      | l.degroot@malvern.example    | Malvern Solicitors |
      | guscioraonline@gmail.example | Rey Gusciora       |

  Scenario: Org admins can add existing users to their organisation
    Given I am logged in as "l.degroot@malvern.example" with password "DigidepsPass1234"
    When I go to "/org/settings/organisation"
    And I follow "Add user"
    And I follow "Cancel"
    Then the URL should match "/org/settings/organisation/\d+"
    When I follow "Add user"
    And I fill in the following:
      | organisation_member_firstname  | Landon Francis              |
      | organisation_member_lastname   | Swayzer-Lacasse             |
      | organisation_member_email      | l.swayzer@outsource.example |
      | organisation_member_roleName_1 | ROLE_PROF_TEAM_MEMBER       |
    And I press "Save"
    Then the URL should match "/org/settings/organisation/\d+"
    And I should see "Landon Swayzer"
    And I should see "l.swayzer@outsource.example"

  Scenario: Org admins can add new users to their organisation
    Given I am logged in as "l.degroot@malvern.example" with password "DigidepsPass1234"
    When I go to "/org/settings/organisation"
    And I follow "Add user"
    And I fill in the following:
      | organisation_member_firstname  | Yvonne                         |
      | organisation_member_lastname   | Lacasse                        |
      | organisation_member_email      | y.lacasse@malvern.example |
      | organisation_member_roleName_1 | ROLE_PROF_TEAM_MEMBER          |
    And I press "Save"
    Then the URL should match "/org/settings/organisation/\d+"
    And I should see "Yvonne Lacasse"
    And I should see "y.lacasse@malvern.example"

  Scenario: Admins of orgs with an email address identifier can add users to their organisation
    Given I am logged in as "guscioraonline@gmail.example" with password "DigidepsPass1234"
    When I go to "/org/settings/organisation"
    And I follow "Add user"
    And I fill in the following:
      | organisation_member_firstname  | Lacy                              |
      | organisation_member_lastname   | Wallen                            |
      | organisation_member_email      | l.wallen@cross-domain.example.com |
      | organisation_member_roleName_1 | ROLE_PROF_TEAM_MEMBER             |
    And I press "Save"
    Then the URL should match "/org/settings/organisation/\d+"
    And I should see "Lacy Wallen"

#    TODO: uncomment once DDPB-3356 is merged
#  Scenario: Org admins can edit non-activated users
#    Given I am logged in as "l.degroot@malvern.example" with password "DigidepsPass1234"
#    When I go to "/org/settings/organisation"
#    And I click on "edit" in the "team-user-yvonnelacassemalvernexample" region
#    Then the "organisation_member_firstname" field should contain "Yvonne"
#    And the "organisation_member_lastname" field should contain "Lacasse"
#    And the "organisation_member_email" field should contain "yvonne.lacasse@malvern.example"
#    When I follow "Cancel"
#    Then the URL should match "/org/settings/organisation/\d+"
#    When I click on "edit" in the "team-user-yvonnelacassemalvernexample" region
#    And I fill in "organisation_member_email" with "y.lacasse@malvern.example"
#    And I press "Save"
#    Then the URL should match "/org/settings/organisation/\d+"
#    And I should see "Yvonne Lacasse"
#    And I should see "y.lacasse@malvern.example"

  Scenario: Org admins can resend activation emails to non-activated users
    Given I am logged in as "l.degroot@malvern.example" with password "DigidepsPass1234"
    When I go to "/org/settings/organisation"
    And I click on "send-activation-email" in the "team-user-ylacassemalvernexample" region
    Then the form should be valid

  Scenario: Org admins cannot resend email to activated users
    Given I open the activation page for "y.lacasse@malvern.example"
    And I fill in the following:
        | set_password_password_first  | DigidepsPass1234 |
        | set_password_password_second | DigidepsPass1234 |
    And I check "set_password_showTermsAndConditions"
    And I press "Submit"
    When I am logged in as "l.degroot@malvern.example" with password "DigidepsPass1234"
    And I go to "/org/settings/organisation"
    Then I should see "Edit" in the "team-user-ylacassemalvernexample" region
    And I should not see "Resend activation email" in the "team-user-ylacassemalvernexample" region

  Scenario: Org team members can edit themselves
    Given I am logged in as "y.lacasse@malvern.example" with password "DigidepsPass1234"
    When I go to "/org/settings/organisation"
    And I click on "edit" in the "team-user-ylacassemalvernexample" region
    Then I should be on "/org/settings/your-details/edit"

  Scenario: Org team members cannot edit or remove other users
    Given I am logged in as "y.lacasse@malvern.example" with password "DigidepsPass1234"
    When I go to "/org/settings/organisation"
    Then I should not see "Add user"
    And I should not see "Edit" in the "team-user-ldegrootmalvernexample" region
    And I should not see the "delete" link
    And I should not see the "send-activation-email" link

  Scenario: Org admins can edit themselves from the organisation page
    Given I am logged in as "l.degroot@malvern.example" with password "DigidepsPass1234"
    When I go to "/org/settings/organisation"
    And I click on "edit" in the "team-user-ldegrootmalvernexample" region
    Then I should be on "/org/settings/your-details/edit"

  Scenario: Org admins can add other admins
    Given I am logged in as "l.degroot@malvern.example" with password "DigidepsPass1234"
    When I go to "/org/settings/organisation"
    And I follow "Add user"
    And I fill in the following:
      | organisation_member_firstname  | Keneth                   |
      | organisation_member_lastname   | Damore                   |
      | organisation_member_email      | k.damore@malvern.example |
      | organisation_member_roleName_1 | ROLE_PROF_ADMIN          |
    And I press "Save"
    Then I should see "k.damore@malvern.example"

  Scenario: Additional org admins can edit and remove users
    Given I open the activation page for "k.damore@malvern.example"
    And I fill in the following:
        | set_password_password_first  | DigidepsPass1234 |
        | set_password_password_second | DigidepsPass1234 |
    And I check "set_password_showTermsAndConditions"
    And I press "Submit"
    When I am logged in as "k.damore@malvern.example" with password "DigidepsPass1234"
    And I go to "/org/settings/organisation"
    Then I should see "Add user"
    And I should see "Edit" in the "team-user-ylacassemalvernexample" region
    And I should see "Remove" in the "team-user-ylacassemalvernexample" region

  Scenario: Org admins can delete colleagues in their organisation
    Given I am logged in as "l.degroot@malvern.example" with password "DigidepsPass1234"
    When I go to "/org/settings/organisation"
    And I click on "delete" in the "team-user-ylacassemalvernexample" region
    Then I should see "Are you sure you want to remove this user from this organisation?"
    And I should see "Yvonne Lacasse"
    And I should see "y.lacasse@malvern.example"
    When I press "Yes, remove user from this organisation"
    Then the URL should match "/org/settings/organisation/\d+"
    And I should not see "Yvonne Lacasse"
    And I should not see "y.lacasse@malvern.example"
