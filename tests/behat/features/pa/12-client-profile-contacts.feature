Feature: PA client profile Notes

  @shaun
  Scenario: PA view client contacts
    Given I load the application status from "pa-report-completed"
    And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000010" region
    Then each text should be present in the corresponding region:
      | No contacts    | client-profile-contacts |

    @shaun
  Scenario: PA adds client contact
    Given I load the application status from "pa-report-completed"
    And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000010" region
    And I save the current URL as "report-overview"
    And I click on "add-contact-button" in the "client-profile-contacts" region
    # empty form
    When I fill in the following:
      | client_contact_firstName       |  |
      | client_contact_lastName        |  |
      | client_contact_jobTitle        |  |
      | client_contact_phone           |  |
      | client_contact_email           |  |
      | client_contact_orgName         |  |
      | client_contact_address1        |  |
      | client_contact_address2        |  |
      | client_contact_address3        |  |
      | client_contact_addressPostcode |  |
      | client_contact_addressCountry  |  |
  And I press "client_contact_save"
    Then the following fields should have an error:
      | client_contact_firstName |
      | client_contact_lastName |
# title > 150 chars form
When I fill in the following:
      | client_contact_firstName          | 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 1 |
      | client_contact_lastName           | 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 1 |
  And I press "client_contact_save"
  Then the following fields should have an error:
    | client_contact_firstName |
    | client_contact_lastName |
  # title < 2 chars form
  When I fill in the following:
    | client_contact_firstName          | 1 |
    | client_contact_lastName           | 1 |
  And I press "client_contact_save"
  Then the following fields should have an error:
    | client_contact_firstName |
    | client_contact_lastName |
Then I fill in the following:
    | client_contact_firstName       | Doc |
    | client_contact_lastName        | Brown |
    | client_contact_jobTitle        | Doctor |
    | client_contact_phone           | 1234512345 |
    | client_contact_email           | doc@brown.com |
    | client_contact_orgName         | BTTF Medical |
    | client_contact_address1        | 1640 Riverside Drive |
    | client_contact_address2        | Twin Pines Estates |
    | client_contact_address3        |  |
    | client_contact_addressPostcode        | AB14BE |
    | client_contact_addressPostcode        | AB14BE |
    | client_contact_addressCountry  | GB |
  And I press "client_contact_save"
    Then the form should be valid
  Then I go to the URL previously saved as "report-overview"
  And the response status code should be 200
    Then each text should be present in the corresponding region:
      | Doc Brown | client-profile-contacts-display-contact |
      | Doctor | client-profile-contacts-display-contact |
      | BTTF Medical | client-profile-contacts-display-organisation |
      | 1234512345 | client-profile-contacts-display-contact-info |
      | doc@brown.com | client-profile-contacts-display-contact-info |

  @shaun
  Scenario: PA edits client contact
  Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
  And I click on "pa-report-open" in the "client-1000010" region
  And I save the current URL as "report-overview"
  And I click on "edit-contact-button" in the "client-profile-contacts-display-actions" region
  Then I fill in the following:
    | client_contact_firstName       | Doce |
    | client_contact_lastName        | Browne |
    | client_contact_jobTitle        | Doctore |
    | client_contact_phone           | 555-1234512345 |
    | client_contact_email           | doce@brown.com |
    | client_contact_orgName         | BTTF Medicale |
    | client_contact_address1        | 1640 Riverside Drivee |
    | client_contact_address2        | Twin Pines Estatese |
    | client_contact_address3        | e |
    | client_contact_addressPostcode        | AB14BEe |
    | client_contact_addressCountry  | US |
  Then the form should be valid
  And I press "client_contact_save"
  Then I go to the URL previously saved as "report-overview"
  And the response status code should be 200
  Then each text should be present in the corresponding region:
    | Doce Browne | client-profile-contacts-display-contact |
    | Doctore | client-profile-contacts-display-contact |
    | BTTF Medicale | client-profile-contacts-display-organisation |
    | 555-1234512345 | client-profile-contacts-display-contact-info |
    | doce@brown.com | client-profile-contacts-display-contact-info |

  @shaun
Scenario: PA delete client contacts
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000010" region
    And I save the current URL as "report-overview"
    And I click on "delete-contact-button" in the "client-profile-contacts" region
    Then the URL should match "/contact/\d+/delete"
    # test cancel button on confirmation page
    When I click on "confirm-cancel"
    Then I go to the URL previously saved as "report-overview"

    # actual delete this time
    Then I click on "delete-contact-button" in the "client-profile-contacts" region
    Then the URL should match "/contact/\d+/delete"
    And I click on "contact-delete"
    Then the form should be valid
    And the response status code should be 200
    Then I go to the URL previously saved as "report-overview"
    And I should not see "Doc Brown" in the "client-profile-contacts" region

