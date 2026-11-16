import { test } from "@playwright/test";
import { createFixtureViaApi, Scenario, setupFixture } from "./fixtures/fixtures";

test("reports are organised according to their status", async ({ page }) => {
  const runTest = async (scenarios: Scenario[]) => {
  }

  await setupFixture(
    // unsubmitted report on closed court order
    createFixtureViaApi("/fixtures/scenarios/generic", [{
      reportType: "OPG102",
      active: false,
      reports: [
        {
          startDate: new Date("2024-01-02")
        }
      ],
      deputies: [{ ref: "restore-report-user-1", type: "LAY" }],
    }]),
  ).then((fixture) => runTest(fixture.data as Scenario[]));
});
