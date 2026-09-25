import { expect, test } from "@playwright/test";
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

    const adminChecklistPage = new AdminChecklistPage(page, submittedReport.id);
    const adminChecklistSubmittedPage = new AdminChecklistSubmittedPage(page, submittedReport.id);

    // complete checklist as super admin
    const adminLoginPage = new AdminLoginPage(page);
    await adminLoginPage.loginAdmin(getAdminUserFixture());

    await adminChecklistPage.goto();
    await adminChecklistPage.markOPG103SectionsSatisfactory();
    await adminChecklistPage.submitChecklistAndContinue();

    await adminChecklistSubmittedPage.isExpected();

    // store submitter and submit date shown after submitting checklist
    await adminChecklistPage.goto();
    const submissionDetails1 = await adminChecklistPage.getSubmissionDetails();

    // logout as super admin
    const adminLogoutPage = new AdminLogoutPage(page);
    await adminLogoutPage.goto();

    // login as second admin user
    await adminLoginPage.loginAdmin(user);

    // go to checklist-submitted URL as second admin user
    await adminChecklistSubmittedPage.goto();

    // ensure checklist has not been resubmitted (see DDLS-1694):
    // check submitter and submit date have not changed; if they have, a GET
    // to this page has resubmitted the checklist, which means it is non-idempotent (BAD)
    await adminChecklistPage.goto();
    const submissionDetails2 = await adminChecklistPage.getSubmissionDetails();
    expect(submissionDetails1 === submissionDetails2);
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
