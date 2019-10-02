Feature: Users can edit members of their organisation

  @prof
  Scenario: Users can add existing users to their organisation
    Given I am logged in as "behat-prof-admin@publicguardian.gov.uk" with password "Abcd1234"
    When I go to "/org/settings/organisation"
    And I follow "Add user"
    And I fill in "organisation_member_email" with "behat-prof-team-member@publicguardian.gov.uk"
    And I press "Save"
    Then the URL should match "/org/settings/organisation/\d+"
    And I should see "Professional Team Member"
    And I should see "behat-prof-team-member@publicguardian.gov.uk"

  @prof
  Scenario: Users can add new users to their organisation
    Given I am logged in as "behat-prof-admin@publicguardian.gov.uk" with password "Abcd1234"
    And emails are sent from "deputy" area
    When I go to "/org/settings/organisation"
    And I follow "Add user"
    And I press "Save"
    Then the form should be invalid
    When I fill in the following:
      | organisation_member_email     | y.lacasse@publicguardian.gov.uk |
    And I press "Save"
    Then the form should be invalid
    When I fill in the following:
      | organisation_member_firstname | Yvonne                          |
      | organisation_member_lastname  | Lacasse                         |
      | organisation_member_email     | y.lacasse@publicguardian.gov.uk |
    And I press "Save"
    Then the URL should match "/org/settings/organisation/\d+"
    And I should see "Yvonne Lacasse"
    And I should see "y.lacasse@publicguardian.gov.uk"
    And the last email should have been sent to "y.lacasse@publicguardian.gov.uk"
    And the last email should contain "Activate your account"
    And the last email should contain "/user/activate"

  @prof
  Scenario: Users can edit non-activated users
    Given I am logged in as "behat-prof-admin@publicguardian.gov.uk" with password "Abcd1234"
    When I go to "/org/settings/organisation"
    And I click on "edit" in the "team-user-ylacassepublicguardiangovuk" region
    Then the "organisation_member_firstname" field should contain "Yvonne"
    And the "organisation_member_lastname" field should contain "Lacasse"
    And the "organisation_member_email" field should contain "y.lacasse@publicguardian.gov.uk"
    When  I fill in "organisation_member_email" with "yvonne.lacasse@publicguardian.gov.uk"
    And I press "Save"
    Then the URL should match "/org/settings/organisation/\d+"
    And I should see "Yvonne Lacasse"
    And I should see "yvonne.lacasse@publicguardian.gov.uk"

  @prof
  Scenario: Users can resend activation emails to non-activated users
    Given I am logged in as "behat-prof-admin@publicguardian.gov.uk" with password "Abcd1234"
    And emails are sent from "deputy" area
    When I go to "/org/settings/organisation"
    And I click on "send-activation-email" in the "team-user-yvonnelacassepublicguardiangovuk" region
    Then the last email should have been sent to "yvonne.lacasse@publicguardian.gov.uk"
    And the last email should contain "Activate your account"
    And the last email should contain "/user/activate"

  @prof
  Scenario: Users cannot resend email to activated users
    Given emails are sent from "deputy" area
    When I open the "/user/activate/" link from the email
    And I activate the user with password "Abcd1234"
    And I am logged in as "behat-prof-admin@publicguardian.gov.uk" with password "Abcd1234"
    And I go to "/org/settings/organisation"
    Then I should see "Edit" in the "team-user-yvonnelacassepublicguardiangovuk" region
    Then I should not see "Resend activation email" in the "team-user-yvonnelacassepublicguardiangovuk" region

  @prof
  Scenario: Org user can log in and see org client
    Given I am logged in as "behat-prof-admin@publicguardian.gov.uk" with password "Abcd1234"
    Then I go to "/org/?limit=50"
    And I should see the "client-103-5" region
    And I should see the "client-102-5" region
    And I should see the "client-104-5" region
    And I should see the "client-102-4-5" region
    And I should see the "client-103-4-5" region
    And I should see the "client" region exactly "30" times

  @prof
  Scenario: Users can delete colleagues in their organisation
    Given I am logged in as "behat-prof-admin@publicguardian.gov.uk" with password "Abcd1234"
    When I go to "/org/settings/organisation"
    And I click on "delete" in the "team-user-yvonnelacassepublicguardiangovuk" region
    Then I should see "Are you sure you want to remove this user from this organisation?"
    And I should see "Yvonne Lacasse"
    And I should see "yvonne.lacasse@publicguardian.gov.uk"
    When I press "Yes, remove user from this organisation"
    Then the URL should match "/org/settings/organisation/\d+"
    And I should not see "Yvonne Lacasse"
    And I should not see "yvonne.lacasse@publicguardian.gov.uk"
