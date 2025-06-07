import { Given, When, Then } from '@badeball/cypress-cucumber-preprocessor';

Given('I am on the weather forecast page', () => {
    cy.visit('/');
});

When('I enter {string} in the city field', (cityName) => {
    cy.get('input#city_form_city').type(cityName);
});

When('I click the {string} button', (buttonText) => {
    cy.get('button:contains("' + buttonText + '")').click();
    // Or be more specific if needed: cy.get('button[type="submit"]').click();
});

Then('I should see {string}', (text) => {
    cy.contains(text).should('be.visible');
});

Then('I should not see {string}', (text) => {
    cy.contains(text).should('not.exist');
});
