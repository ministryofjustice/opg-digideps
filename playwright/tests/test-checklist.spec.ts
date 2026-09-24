import { test } from "@playwright/test";
import {
  createFixtureViaApi,
  Scenario,
  setupFixture,
} from "./fixtures/fixtures";
import AdminChecklistPage from "./pages/AdminChecklistPage";

const deputyReference = "test-checklist-user";

test("visiting the checklist submitted page does not resubmit checklist", async ({
  page,
}) => {
  const runTest = async (scenario: Scenario) => {
    // const reportId = scenario.orders[0].reports[0].id;
    const submittedReport = scenario.orders[0].reports.find(
      (reportDetails) => reportDetails.submitted
    );

    if (submittedReport === undefined) {
      throw new Error("We shouldn't be here");
    }



    const adminChecklistPage = new AdminChecklistPage(page, submittedReport.id);
    await adminChecklistPage.goto();
  };

  // create a single unsubmitted, but ready to submit, report, with a document
  // that doesn't have a corresponding S3 object
  await setupFixture(
    createFixtureViaApi(
      "/fixtures/scenarios/laysimple",
      {
        deputyReference: deputyReference,
      },
    ),
  ).then((fixture) => runTest(fixture.data as Scenario));
});
