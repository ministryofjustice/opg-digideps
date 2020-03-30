Feature: Organisation deputyship updates

  Scenario: Apply deputyship updates via CSV
    Given emails are sent from "admin" area
    And I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I create a new "NDR-disabled" "prof named" user "New Dep1" "Surname1" with email "new-behat-prof1@publicguardian.gov.uk" and postcode "SW1"
    And I activate the named deputy with password "Abcd1234"
    Then I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I create a new "NDR-disabled" "prof named" user "New Dep2" "Surname2" with email "behat-prof1@example.com1" and postcode "SW2"
    And I activate the named deputy with password "Abcd1234"
    Then I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I create a new "NDR-disabled" "prof named" user "New Dep3" "Surname3" with email "behat-prof1@example.com2" and postcode "SW3"
    And I activate the named deputy with password "Abcd1234"
    Then I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    # upload PROF updates
    When I go to admin page "/admin/org-csv-upload"
    When I attach the file "behat-prof-org-updates.csv" to "admin_upload_file"
    And I press "admin_upload_upload"
    And the organisation "publicguardian.gov.uk" is active
    And "new-behat-prof1@publicguardian.gov.uk" has been added to the "publicguardian.gov.uk" organisation
    And the organisation "example.com1" is active
    And "behat-prof1@example.com1" has been added to the "example.com1" organisation
    And the organisation "example.com2" is active
    And "behat-prof1@example.com2" has been added to the "example.com2" organisation

  # Client has different deputy in the same org. Old deputy left org - dont delete client
  Scenario: Professional deputy leaves organisation, clients appointed a new deputy within the same organisation
    #  (deputy number changes, org identifier stays the same, different deputy email of same org)
    Given "behat-prof1@publicguardian.gov.uk" has been removed from the "publicguardian.gov.uk" organisation
    # Assert new deputy can see client
    And I am logged in as "new-behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    Then I should see the "client-01000010" region
    # Assert client still associated with same org
    Then I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "Abcd1234"
    When I visit the client page for "01000010"
    # Assert same organisation
    And I should see "PA OPG" in the "assigned-organisation" region
    # Assert new named deputy within same organisation
    And I should see "new-behat-prof1@publicguardian.gov.uk" in the "deputy-details" region

  # Client has same deputy in a new org - don't delete client
  Scenario: Professional deputy leaves organisation, and retains their clients
    #  (deputy number stays the same, org identifier changes - example.com1)
    Given "behat-prof1@publicguardian.gov.uk" has been removed from the "publicguardian.gov.uk" organisation
    # Assert new deputy has retained clients
    And I am logged in as "behat-prof1@example.com1" with password "Abcd1234"
    Then I should see the "client-1138393T" region
    # Assert client associated with new org
    Then I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "Abcd1234"
    When I visit the client page for "1138393t"
    # Assert same named deputy within new organisation
    Then each text should be present in the corresponding region:
      | Your Organisation (example.com1)                        | assigned-organisation |
      | NEW DEP2 NEW SURNAME2                 | named-deputy-fullname |
      | Prof Example 1                        | deputy-details |
      | ADD2                                  | deputy-details |
      | ADD3                                  | deputy-details |
      | ADD4                                  | deputy-details |
      | ADD5                                  | deputy-details |
      | SW2                                   | deputy-details |
      | GB                                    | deputy-details |
      | behat-prof1@example.com1              | deputy-details |

  # Client has new deputy and new org - delete client and expect new one created
  Scenario: Clients appointed to a new organisation
    #  (deputy number changes, org identifier changes to deputy of new organisation - example.com2)
    Given I am logged in as "behat-prof1@example.com2" with password "Abcd1234"
    And I should see the "client-11498120" region
    # Assert client associated with new org
    Then I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "Abcd1234"
    # Assert new organisation for new client
    When I visit the client page for "11498120"
    Then each text should be present in the corresponding region:
      | Your Organisation (example.com2)      | assigned-organisation |
      | NEW DEP3 NEW SURNAME3                 | named-deputy-fullname |
      | Prof Example 2                        | deputy-details |
      | ADD2                                  | deputy-details |
      | ADD3                                  | deputy-details |
      | ADD4                                  | deputy-details |
      | ADD5                                  | deputy-details |
      | SW3                                   | deputy-details |
      | GB                                    | deputy-details |
      | behat-prof1@example.com2              | deputy-details |
