@v2 @v2_reporting_2 @deputy-costs-estimate
Feature: Deputy costs estimates - Applies to Professionals with deputies of all report types

    @prof-team-hw-not-started
    Scenario: A users costs are estimated to be fixed
        Given a Professional Team Deputy has not started a health and welfare report
        When I navigate to and start the deputy costs estimates report section
        And I choose fixed costs
        Then the deputy costs estimate summary page should contain the details I entered

    @prof-team-hw-not-started
    Scenario: A users costs are estimated to be assessed
        Given a Professional Team Deputy has not started a health and welfare report
        When I navigate to and start the deputy costs estimates report section
        And I choose assessed costs
        And I fill in the estimated assessed amounts correctly
        And I have information that would explain these estimated costs
        Then the deputy costs estimate summary page should contain the details I entered

    @prof-team-hw-not-started
    Scenario: A users costs are estimated to be both fixed and assessed
        Given a Professional Team Deputy has not started a health and welfare report
        When I navigate to and start the deputy costs estimates report section
        And I choose fixed and assessed costs
        And I fill in the estimated fixed and assessed costs correctly
        And I do not have information that would explain these estimated costs
        Then the deputy costs estimate summary page should contain the details I entered

    @prof-team-hw-completed
    Scenario: A user goes back and edits existing responses
        Given a Professional Team Deputy has completed a health and welfare report
        When I visit the report overview page
        Then I should see "prof-deputy-costs-estimate" as "finished"
        When I follow link to deputy costs estimate
        Then I should be on the deputy costs estimate summary page
        When I edit how i will charge for my services
        Then the deputy costs estimate summary page should contain the details I entered
        When I edit how much I expect to charge
        Then the deputy costs estimate summary page should contain the details I entered
        When I edit information that will explain expected costs
        Then the deputy costs estimate summary page should contain the details I entered
        When I edit the costs breakdown
        Then the deputy costs estimate summary page should contain the details I entered

    @prof-team-hw-not-started
    Scenario: A user enters invalid details
        Given a Professional Team Deputy has not started a health and welfare report
        When I navigate to and start the deputy costs estimates report section
        And I check that if I choose nothing for costs type
        Then I get a cost type validation error
        When I enter invalid values for expected costs
        Then I get an expected costs validation error
        When I do not enter an option to explain estimated costs
        Then I get an explain estimated costs validation error
