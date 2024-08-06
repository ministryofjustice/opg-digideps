@v2 @v2_reporting_2 @money-out
Feature: Money Out

    @lay-pfa-high-not-started
    Scenario: A user attempts to not enter any payments
        Given a Lay Deputy has not started a Pfa High Assets report
        And I visit the report overview page
        Then I should see "money-out" as "not started"
        When I view and start the money out report section
        And I confirm "Yes" to taking money out on the clients behalf
        Then I select from the money out payment options
        And I try to save and continue without adding a payment
        Then I should see correct money out validation message

    @lay-pfa-high-not-started
    Scenario: A user adds one of each payment type
        Given a Lay Deputy has not started a Pfa High Assets report
        When I view and start the money out report section
        And I confirm "Yes" to taking money out on the clients behalf
        Then I select from the money out payment options
        And I add one type of money out payment from each category
        Then I should see the expected results on money out summary page
        When I follow link back to report overview page
        Then I should see "money-out" as "9 items"

    @lay-pfa-high-not-started
    Scenario: A user removes a payment
        Given a Lay Deputy has not started a Pfa High Assets report
        When I view and start the money out report section
        And I confirm "Yes" to taking money out on the clients behalf
        And I add one money out payment
        When I add another money out payment from an existing account
        When I visit the money out summary section
        And I remove an existing money out payment
        Then I should see the expected results on money out summary page

    @lay-pfa-high-completed
    Scenario: A user edits an existing payment
        Given a Lay Deputy has completed a Pfa High Assets report
        When I visit the money out summary section
        And I edit an existing money out payment
        Then I should see the expected results on money out summary page

    @lay-pfa-high-not-started
    Scenario: A user adds an additional payment
        Given a Lay Deputy has not started a Pfa High Assets report
        When I view and start the money out report section
        And I confirm "Yes" to taking money out on the clients behalf
        And I add one money out payment
        When I add another money out payment from an existing account
        Then I should see the expected results on money out summary page

    @lay-pfa-high-not-started
    Scenario: A user tries to add a one off payment of less than £1k
        Given a Lay Deputy has not started a Pfa High Assets report
        When I view and start the money out report section
        And I confirm "Yes" to taking money out on the clients behalf
        And I add a payment without filling in description and amount
        Then I should see correct money out description and amount validation message

    @lay-pfa-high-not-started
    Scenario: A Lay user can see the Fees charged by a solicitor, accountant or other professional option
        Given a Lay Deputy has not started a Pfa High Assets report
        When I view and start the money out report section
        And I confirm "Yes" to taking money out on the clients behalf
        And I add the Fees charged by a solicitor, accountant or other professional payment
        Then I should see the expected results on money out summary page

    @pa-named-pfa-high-not-started
    Scenario: A Public Authority user can see the professional fees not including deputy costs option
        Given a Public Authority Named Deputy has not started a Pfa High Assets report
        When I view and start the money out report section
        And I confirm "Yes" to taking money out on the clients behalf
        And I add the Fees charged by a solicitor, accountant or other professional payment not including deputy costs
        Then I should see the expected results on money out summary page

    @prof-named-pfa-high-not-started
    Scenario: A Professional user can see the professional fees not including deputy costs option
        Given a Professional Deputy has not started a Pfa High Assets report
        When I view and start the money out report section
        And I confirm "Yes" to taking money out on the clients behalf
        And I add the Fees charged by a solicitor, accountant or other professional payment not including deputy costs
        Then I should see the expected results on money out summary page

    @lay-pfa-high-not-started
    Scenario: A Lay user reports having no money out to report
        Given a Lay Deputy has not started a report
        When I view and start the money out report section
        And I confirm "No" to taking money out on the clients behalf
        And I enter a reason for no money out
        Then I should see the expected results on money out summary page


    @lay-pfa-high-not-started
    Scenario: Transaction items over £1k are restored when user accidentally changes answer to reporting no money out
        Given a Lay Deputy has not started a report
        When I view and start the money out report section
        And I confirm "Yes" to taking money out on the clients behalf
        Then I select from the money out payment options
        And I add the Fees charged by a solicitor, accountant or other professional payment
        Then the money out summary page should contain "1" money in values
        When I edit the money out exist summary section
        And I confirm "No" to taking money out on the clients behalf
        And I enter a reason for no money out
        Then the money out summary page should contain "no" money in values
        Then I edit the money out exist summary section
        And I confirm "Yes" to taking money out on the clients behalf
        Then the money out summary page should contain "1" money in values

    @lay-pfa-high-not-started
    Scenario: A user adds a transaction item and then removes it and reports to having no money out then adds a new transaction item
        Given a Lay Deputy has not started a report
        When I view and start the money out report section
        And I confirm "Yes" to taking money out on the clients behalf
        Then I select from the money out payment options
        And I add the Fees charged by a solicitor, accountant or other professional payment
        Then the money out summary page should contain "1" money in values
        When I delete the money out transaction item from the summary page
        Then the money out summary page should contain "no" money in values
        When I add a new money out payment
        Then the money out summary page should contain "1" money in values
