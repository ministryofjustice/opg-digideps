Feature: deputy / report / Formatted Report

    @deputy
    Scenario: The opg report should contain all the required sections
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I view the formatted report
        Then I should see "Deputy report for property and financial decisions"
        And I should see "Section 1"
        And I should see "123456ABC"
        And I should see "Section 2"
        And I should see "3 beds" in "decisions-section"
        And I should see "the client was able to decide at 85%" in "decisions-section"
        And I should see "2 televisions" in "decisions-section"
        And I should see "the client said he doesnt want a tv anymore" in "decisions-section"
        And I should see "Section 3"
        And I should see "Andy White" in "contacts-section"
        And I should see "Fred Smith" in "contacts-section"
        Then I should see "Section 4"
        And I should see "Safeguarding"
        And the report should indicate that the "Yes" checkbox for "Do you live with the client" is checked
        And I should see "Section  6"
        And I should see "HSBC - main account" in "accounts-section"
        And I should see "Section  7"
        And I should see "Client’s assets and debts"
        Then the 1 asset group should be "Property"
        And the 2 asset group should be "Vehicles"
        And I should see "More info."
  
