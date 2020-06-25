Feature: Create a CourtOrder when a Lay deputy has NDR enabled in admin
  In order to ensure that NDR enabled deputies exist within a CourtOrder
  As a system
  I need to create a CourtOrder when a deputy becomes NDR enabled if one does not exist

  Scenario: Admin set NDR enabled flag for a deputy
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And the following users exist:
      | ndr      | deputyType | firstName | lastName    | email                 | postCode | activated |
      | disabled | LAY        | Jim       | Green       | jim.green@test.com    | HA4      | true      |
    And the following clients exist and are attached to deputies:
      | firstName | lastName | phone       | address     | address2  | county  | postCode | caseNumber | deputyEmail            |
      | Dory      | Smyth    | 01215552222 | 1 Fake Road | Fakeville | Faketon | B4 6HQ   | DS124323   | jim.green@test.com     |
    And I go to admin page "/"
    When I "enable" NDR for user "jimgreentestcom"
    Then a court order should exist between "jim.green@test.com" and "dory"

