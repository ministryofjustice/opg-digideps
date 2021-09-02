@contact-details @v2
Feature: Contact details
    Scenario: Home screen should show lay deputy email address
        When I visit the client login page
        Then the support footer should show "laydeputysupport@publicguardian.gov.uk"

    @admin
    Scenario: Admin should not show any helpline
        When I visit the admin login page
        Then the support footer should not be visible
        Given an admin user accesses the admin app
        And the support footer should not be visible

    @ndr-not-started
    Scenario: NDR should see lay email
        Given a Lay Deputy has not started an NDR report
        When I visit the report overview page
        And the support footer should show "laydeputysupport@publicguardian.gov.uk"

    @lay-health-welfare-not-started
    Scenario: Lay deputy should see lay email
        Given a Lay Deputy has not started a Health and Welfare report
        When I visit the report overview page
        Then the support footer should show "laydeputysupport@publicguardian.gov.uk"

    @prof-team-hw-not-started
    Scenario: Professional deputy should see professional helpline
        Given a Professional Team Deputy has not started a health and welfare report
        When I visit the report overview page
        Then the support footer should show "opg.pro@publicguardian.gov.uk"

    @pa-admin-health-welfare-not-started
    Scenario: Public authority deputy should see professional helpline
        Given a Public Authority Admin Deputy has not started a report
        When I visit the report overview page
        Then the support footer should show "opg.publicauthorityteam@publicguardian.gov.uk"
