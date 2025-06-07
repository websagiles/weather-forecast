Feature: Weather Forecast Application
    As a user
    I want to get the weather forecast for a city
    So that I can plan my activities

    Scenario: User searches for weather in a valid city
        Given I am on the weather forecast page
        When I enter "Berlin" in the city field
        And I click the "Get Weather" button
        Then I should see "Weather in Berlin"
        And I should see "Temperature:"

    Scenario: User searches for a city that is not found
        Given I am on the weather forecast page
        When I enter "NonExistentCity123" in the city field
        And I click the "Get Weather" button
        Then I should see "City \"NonExistentCity123\" not found."

    Scenario: User submits an empty city name
        Given I am on the weather forecast page
        When I click the "Get Weather" button
        Then I should see "Please enter a city name."
