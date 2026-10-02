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
           { startDate: new Date(), submitDate: new Date() },

           // submitted but incomplete
           {
             startDate: new Date(),
             submitDate: new Date(),
             unSubmitDate: new Date()
           },

           // active (note this has to be last in this list otherwise
           // it does not become the current report, due to how the FixtureService
           // works)
           { startDate: new Date() }
         ],
         deputies: [
           { ref: "client-details-user-1", type: "LAY" }
         ]
      }
    ),
  ).then((fixture) => runTest(fixture.data as Scenario));
});
