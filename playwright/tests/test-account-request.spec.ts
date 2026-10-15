import { test } from "@playwright/test";
import {
  createFixtureViaApi,
  Scenario,
  setupFixture,
} from "./fixtures/fixtures";
import AccountRequestPage from "./pages/AccountRequestPage";

test("user can request an account if they enter valid details", async ({
  page,
}) => {
  const deputyRef = "account-request-user-1";

  const runTest = async (scenario: Scenario) => {
    const deputy = scenario.deputies[deputyRef];
    const client = scenario.client;

    const accountRequestPage = new AccountRequestPage(page);
    await accountRequestPage.goto();
    await accountRequestPage.enterDeputyDetails(deputy);
    await accountRequestPage.enterClientDetails(client);
    await accountRequestPage.signUp();

    await accountRequestPage.isExpected();
    accountRequestPage.expectSuccessMessage();
  };

  await setupFixture(
    // creates a deputy and client record, which is what we need
    // in the db to verify the user requesting an account
    createFixtureViaApi("/fixtures/scenarios/generic", {
      deputies: [{ ref: deputyRef, type: "LAY" }],
    }),
  ).then((fixture) => runTest(fixture.data as Scenario));
});
