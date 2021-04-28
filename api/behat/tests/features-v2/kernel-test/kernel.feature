@v2
Feature: Using SymfonyExtension

    @acs
    Scenario: Checking the application's kernel environment
        Then the application's kernel should use "test" environment
