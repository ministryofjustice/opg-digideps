import { test } from "@playwright/test";
import {
  createFixtureViaApi, getAdminUserFixture, getUserFixture,
  Scenario,
  setupFixture,
  TestUser,
} from "./fixtures/fixtures";
import AdminChecklistPage from "./pages/AdminChecklistPage";
import AdminLoginPage from "./pages/AdminLoginPage";
import AdminLogoutPage from "./pages/AdminLogoutPage";
import AdminChecklistSubmittedPage from "./pages/AdminChecklistSubmittedPage";

const deputyReference = "test-checklist-user";

test("visiting the checklist submitted page does not resubmit checklist", async ({
  page,
}) => {
  const runTest = async (scenario: Scenario, user: TestUser) => {
    // const reportId = scenario.orders[0].reports[0].id;
    const submittedReport = scenario.orders[0].reports.find(
      (reportDetails) => reportDetails.submitted
    );

    if (submittedReport === undefined) {
      throw new Error("We shouldn't be here");
    }

    // complete checklist as super admin
    const adminLoginPage = new AdminLoginPage(page)
    await adminLoginPage.loginAdmin(getAdminUserFixture())

    const adminChecklistPage = new AdminChecklistPage(page, submittedReport.id);
    await adminChecklistPage.goto();

    // store submitter and submit date

    const adminLogoutPage = new AdminLogoutPage(page);
    await adminLogoutPage.goto();

    // login second admin user
    await adminLoginPage.loginAdmin(user);

    // go to checklist-submitted URL for report
    const adminChecklistSubmittedPage = new AdminChecklistSubmittedPage(page, submittedReport.id);
    await adminChecklistSubmittedPage.goto();

    // check submitter and submit date have not changed

  };

  // create a single unsubmitted, but ready to submit, report, with a document
  // that doesn't have a corresponding S3 object
  const scenarioPromise = setupFixture(
    createFixtureViaApi(
      "/fixtures/scenarios/laysimple",
      {
        deputyReference: deputyReference,
      },
    ),
  );

  const userPromise = getUserFixture("Admin", "PRO");

  await Promise.all([scenarioPromise, userPromise])
    .then(
      async ([scenario, user]) => {
        await runTest(scenario.data as Scenario, user);
      }
    );
});
