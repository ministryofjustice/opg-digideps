Feature: Organisation deputyship updates

  Scenario: Apply deputyship updates via CSV
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And the following users exist:
      | ndr      | deputyType | firstName | lastName | email                                 | postCode | activated |
      | disabled | PROF       | New Dep1  | Surname1 | new-behat-prof1@publicguardian.gov.uk | SW1      | true      |
      | disabled | PROF       | New Dep2  | Surname1 | behat-prof1@example.com1              | SW2      | true      |
      | disabled | PROF       | New Dep3  | Surname1 | behat-prof1@example.com2              | SW3      | true      |
    # upload PROF updates
    When I go to admin page "/admin/org-csv-upload"
    And I attach the file "behat-prof-org-updates.csv" to "admin_upload_file"
    And I press "admin_upload_upload"
    Then the organisation "publicguardian.gov.uk" is active
    And "new-behat-prof1@publicguardian.gov.uk" has been added to the "publicguardian.gov.uk" organisation
    And the organisation "example.com1" is active
    And "behat-prof1@example.com1" has been added to the "example.com1" organisation
    And the organisation "example.com2" is active
    And "behat-prof1@example.com2" has been added to the "example.com2" organisation

  # Client has different deputy in the same org. Old deputy left org - dont delete client
  Scenario: Professional deputy leaves organisation, clients appointed a new deputy within the same organisation
    # Assert new deputy can see client
    Given I am logged in as "new-behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    Then I should see the "client-01000010" region
    # Assert client still associated with same org
    Then I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I visit the client page for "01000010"
    # Assert same organisation
    And I should see "PA OPG" in the "assigned-organisation" region
    # Assert new named deputy within same organisation
    And I should see "new-behat-prof1@publicguardian.gov.uk" in the "deputy-details" region

  # Client has different deputy in different org. Client remains with current org and deputy
  Scenario: Professional deputy leaves organisation, clients appointed a new deputy within the same organisation
    # Assert new deputy can see client
    Given I am logged in as "new-behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    Then I should see the "client-01000010" region
    # Assert client still associated with same org
    Then I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I visit the client page for "01000010"
    # Assert same organisation
    And I should see "PA OPG" in the "assigned-organisation" region
    # Assert new named deputy within same organisation
    And I should see "new-behat-prof1@publicguardian.gov.uk" in the "deputy-details" region

  # Ensure no auto discharges occur
  # Client has new deputy and new org - delete client and expect new one created
  Scenario: Clients appointed to a new organisation
    #  (deputy number changes, org identifier changes to deputy of new organisation - example.com2)
    Given I am logged in as "behat-prof1@example.com2" with password "DigidepsPass1234"
    And I should not see the "client-11498120" region
    # Assert client still associated with previous org
    Then I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I search in admin for a client with the term "11498120"
    Then I should see "Found 1 clients"
    # Assert old client has not been discharged
    And I should not see the "discharged-client-11498120" region
    And I should not see the "discharged-client-11498120-discharged-on" region
    When I visit the client page for "11498120"
    # Assert same organisation
    Then I should see "PA OPG" in the "assigned-organisation" region
