import { test } from "@playwright/test";
import { createFixtureViaApi, Scenario, setupFixture } from "./fixtures/fixtures";

test("reports are organised according to their status", async ({ page }) => {
  const runTest = (scenarios: Scenario[]) => {
    console.log(scenarios[0].orders[0].reports);
    return true;
  };

  await setupFixture(
    // unsubmitted report on closed court order
    createFixtureViaApi("/fixtures/scenarios/generic", [{
      reportType: "OPG102",
      active: false,
      reports: [
        {
          startDate: new Date("2024-01-02")
        }
      ]
    }]),
  ).then((fixture) => runTest(fixture.data as Scenario[]));
});
