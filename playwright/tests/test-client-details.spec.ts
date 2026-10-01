import { test } from "@playwright/test";
import {
  createFixtureViaApi,
  Scenario,
  setupFixture,
} from "./fixtures/fixtures";

test("reports are displayed in tables according to their status", async ({ page }) => {
  const runTest = async (scenario: Scenario) => {
    console.log(JSON.stringify(scenario, null, 2));
    return Promise.resolve();
  };

  await setupFixture(
    createFixtureViaApi(
      "/fixtures/scenarios/generic",
       {
         reportType: "OPG102",
         reports: [
           {startDate: new Date()},
           {startDate: new Date(), submitDate: new Date()}
         ],
         deputies: [
           {ref: "client-details-user-1", type: "LAY"}
         ]
      }
    ),
  ).then((fixture) => runTest(fixture.data as Scenario));
});
