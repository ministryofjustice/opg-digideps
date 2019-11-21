Feature: Organisation deputyship updates

  @discharge
  Scenario: Apply deputyship updates via CSV
    Given emails are sent from "admin" area
    And I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I create a new "NDR-disabled" "prof named" user "New Dep1" "Surname1" with email "new-behat-prof1@publicguardian.gov.uk" and postcode "SW1"
    And I activate the named deputy with password "Abcd1234"
    Then I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I create a new "NDR-disabled" "prof named" user "New Dep2" "Surname2" with email "behat-prof1@example.com" and postcode "SW2"
    And I activate the named deputy with password "Abcd1234"
    Then I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I create a new "NDR-disabled" "prof named" user "New Dep3" "Surname3" with email "behat-prof1@example.com2" and postcode "SW3"
    And I activate the named deputy with password "Abcd1234"
    Then I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    # upload PROF updates
    When I click on "admin-upload-pa"
    When I attach the file "behat-prof-org-updates.csv" to "admin_upload_file"
    And I press "admin_upload_upload"
    And the organisation "publicguardian.gov.uk" is active
    And "new-behat-prof1@publicguardian.gov.uk" has been added to the "publicguardian.gov.uk" organisation
    And the organisation "example.com" is active
    And "behat-prof1@example.com" has been added to the "behat-prof1@example.com" organisation
    And the organisation "example.com2" is active
    And "behat-prof1@example.com2" has been added to the "example.com2" organisation

  @discharge
  Scenario: Clients appointed to a new organisation
    #  (deputy number changes, org email changes)
    Given I am logged in as "behat-PROF1@example.com2" with password "Abcd1234"
    And I should see the "client-11498120" region
    # assert client has a single accessible report

    Then I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    And I should not see the "client-11498120" region

  @discharge
  Scenario: Professional deputy leaves organisation, and retains their clients
    #  (deputy number stays the same, org identifier changes)
    Given I am logged in as "behat-PROF1@example.com" with password "Abcd1234"
    Then I should see the "client-1138393T" region
    # assert client has old reports accessible
    Then I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    And I should not see the "client-1138393T" region


  Scenario: Professional deputy leaves organisation, clients appointed a new deputy within the same organisation
    #  (deputy number changes, org identifier stays the same, different deputy email)
#    Given the organisation "publicguardian.gov.uk" is active
#    And I am logged in as "behat-prof-admin@publicguardian.gov.uk" with password "Abcd1234"
#    And I should see the "client-10000010" region
#    And I remove "behat-prof1@publicguardian.gov.uk" from the organisation
#    When I click on "org-dashboard" in the navbar region
#    Then I should see the "client-10000010" region
#    When I go to "logout"
#    And I am logged in as "new-behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
#    Then I should see the "client-10000010" region

  Scenario: Professional deputy leaves organisation, clients retained by deputy
    #  (deputy number stays the same, org identifier changes)


