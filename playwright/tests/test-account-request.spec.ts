import { test } from "@playwright/test";
import { createFixtureViaApi, Scenario, setupFixture } from "./fixtures/fixtures";

test("user can request an account if they enter valid details", async () => {
  const deputyRef = "account-request-user-1";

  const runTest = (scenario: Scenario) => {
    console.log(scenario);
    const deputyEmail = scenario.deputies[deputyRef].email;
    return Promise.resolve(scenario);
  };

  await setupFixture(
    // creates a deputy and client record, which is what we need
    // in the db to verify the user requesting an account
    createFixtureViaApi("/fixtures/scenarios/generic", {
      deputies: [{ ref: deputyRef, type: "LAY" }],
    }),
  ).then((fixture) => runTest(fixture.data as Scenario));
});
