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

    // TODO remove once the real account service is available
    await accountRequestPage.selectMockResponseToReceive(
      "Valid details entered",
    );

    await accountRequestPage.signUp();

    await accountRequestPage.isExpected();
    accountRequestPage.expectSuccessMessage();
  };

  await setupFixture(
    // creates a deputy and client record, which is what we need
    // in the db to verify the user requesting an account
    createFixtureViaApi("/fixtures/scenarios/generic", {
      deputies: [{ ref: deputyRef, type: "LAY", hasRegistered: false }],
    }),
  ).then((fixture) => runTest(fixture.data as Scenario));
});

test("user account request is rejected if they enter non-matching details", async ({
  page,
}) => {
  // deputy and client are not in the db, so will not match, despite
  // form validation passing
  const deputy = {
    firstName: "Jet",
    lastName: "Caroom",
    postCode: "B1 2XX",
    email: "jet.caroom@opg.test",
  };

  const client = { id: 1, caseNumber: "1234567890", lastName: "Fraberge-Egg" };

  const accountRequestPage = new AccountRequestPage(page);
  await accountRequestPage.goto();
  await accountRequestPage.enterDeputyDetails(deputy);
  await accountRequestPage.enterClientDetails(client);

  // TODO remove once the real account service is available
  await accountRequestPage.selectMockResponseToReceive("Details not matched");

  await accountRequestPage.signUp();

  await accountRequestPage.isExpected();
  accountRequestPage.expectNotMatchedErrorMessage();
});
