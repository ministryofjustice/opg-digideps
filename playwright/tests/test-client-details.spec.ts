import { test } from "@playwright/test";
import {
  createFixtureViaApi, getAdminUserFixture,
  Scenario,
  setupFixture
} from "./fixtures/fixtures";
import AdminLoginPage from "./pages/AdminLoginPage";
import AdminClientDetailsPage from "./pages/AdminClientDetailsPage";

test("reports are organised according to their status", async ({ page }) => {
  const runTest = async (scenario: Scenario) => {
    console.log(JSON.stringify(scenario, null, 2));
    const clientId = scenario["orders"][0]["clientId"];

    const adminLoginPage = new AdminLoginPage(page);
    await adminLoginPage.loginAdmin(getAdminUserFixture());

    const adminClientDetailsPage = new AdminClientDetailsPage(page, clientId);
    await adminClientDetailsPage.goto()

    return Promise.resolve();
  };

  await setupFixture(
    createFixtureViaApi(
      "/fixtures/scenarios/generic",
       {
         reportType: "OPG102",
         reports: [
           // submitted and complete
           { startDate: new Date('2024-01-02'), submitDate: new Date('2025-01-01') },

           // submitted but incomplete
           {
             startDate: new Date('2025-01-02'),
             submitDate: new Date('2026-01-01'),
             unSubmitDate: new Date('2026-01-03')
           },

           // active (note this has to be last in this list otherwise
           // it does not become the current report, due to how the
           // FixtureService processes the ReportList)
           { startDate: new Date('2026-01-02') }
         ],
         deputies: [
           { ref: "client-details-user-1", type: "LAY" }
         ]
      }
    ),
  ).then((fixture) => runTest(fixture.data as Scenario));
});
