@contact-details @v2 @acs
Feature: Contact details





#    Rewrite as all these functions are not availabel in new world






    Scenario: Home screen should show lay deputy email address
        When I go to "/"
        Then I should see the "contact-details" region
        And I should see "laydeputysupport@publicguardian.gov.uk" in the "contact-details" region

    Scenario: Admin should not show any helpline
        Given I go to admin page "/"
        Then I should not see the "contact-details" region
        When I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
        Then I should not see the "contact-details" region

    @ndr-not-started
    Scenario: NDR should see lay email
        Given a Lay Deputy has not started an NDR report
        When I visit the report overview page
        Then I should see the "contact-details" region
        And I should see "laydeputysupport@publicguardian.gov.uk" in the "contact-details" region

    @lay-health-welfare-not-started
    Scenario: Lay deputy should see lay email
        Given a Lay Deputy has not started a Health and Welfare report
        When I visit the report overview page
        Then I should see the "contact-details" region
        And I should see "laydeputysupport@publicguardian.gov.uk" in the "contact-details" region

    @prof-team-hw-not-started
    Scenario: Professional deputy should see professional helpline
        Given a Professional Team Deputy has not started a health and welfare report
        When I visit the report overview page
        Then I should see the "contact-details" region
        And I should see "opg.pro@publicguardian.gov.uk" in the "contact-details" region

    @pa-admin-not-started
    Scenario: Public authority deputy should see professional helpline
        Given a Public Authority Admin Deputy has not started a report
        When I visit the report overview page
        Then I should see the "contact-details" region
        And I should see "opg.publicauthorityteam@publicguardian.gov.uk" in the "contact-details" region
