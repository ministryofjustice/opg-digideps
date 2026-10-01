import { test } from "@playwright/test";
import {
  createFixtureViaApi,
  Scenario,
  setupFixture,
} from "./fixtures/fixtures";

const deputyReference = "client-details-user";

test("reports are displayed in tables according to their status", async ({ page }) => {
  const runTest = async (scenario: Scenario) => {
    console.log(scenario);
    return Promise.resolve();
  };

  // create multiple reports in different states
  await setupFixture(
    createFixtureViaApi(
      "/fixtures/scenarios/generic",
      {
        "deputies": []
      },
    ),
  ).then((fixture) => runTest(fixture.data as Scenario));
});
