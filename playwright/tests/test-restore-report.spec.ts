import { test } from "@playwright/test";
import { createFixtureViaApi, getAdminUserFixture, Scenario, setupFixture } from "./fixtures/fixtures";
import AdminLoginPage from "./pages/AdminLoginPage";
import AdminClientDetailsPage from "./pages/AdminClientDetailsPage";
import AdminRestoreReportPage from "./pages/AdminRestoreReportPage";

test("a PFA report on a closed court order can be restored over the active PFA report", async ({ page }) => {
  const runTest = async (scenarios: Scenario[]) => {
    console.log(JSON.stringify(scenarios, null, 2));
    const clientId = scenarios[0]["orders"][0]["clientId"];
    const replaceableReportId = scenarios[1]["orders"][0]["reports"][0]["id"];
    const restoreFromReportId = scenarios[0]["orders"][0]["reports"][0]["id"];

    const adminLoginPage = new AdminLoginPage(page);
    await adminLoginPage.loginAdmin(getAdminUserFixture());

    const adminClientDetailsPage = new AdminClientDetailsPage(page, clientId);
    await adminClientDetailsPage.goto();

    // confirm that the report on the active court order is swappable with
    // the report on the closed court order
    await adminClientDetailsPage.restoreReport("2024-2025");

    const adminRestoreReportPage = new AdminRestoreReportPage(page, replaceableReportId, restoreFromReportId);
    await adminRestoreReportPage.isExpected();

    return Promise.resolve();
  };

  await setupFixture(
    createFixtureViaApi("/fixtures/scenarios/generic", [
      // unsubmitted report on closed court order (can replace active report)
      {
        reportType: "OPG102",
        active: false,
        reports: [
          {
            startDate: new Date("2024-01-02")
          }
        ]
      },
      // unsubmitted report on active court order (can be replaced by report
      // on closed order)
      {
        reportType: "OPG102",
        active: true,
        reports: [
          {
            startDate: new Date("2025-01-01")
          }
        ]
      },
    ]),
  ).then((fixture) => runTest(fixture.data as Scenario[]));
});
