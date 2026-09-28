import path from "path";
import { test } from "@playwright/test";
import {
  createFixtureViaApi,
  getAdminUserFixture,
  Scenario,
  setupFixture,
  testPassword,
} from "./fixtures/fixtures";
import AdminDocumentsListPage from "./pages/AdminDocumentsListPage";
import DocumentsUploadPage from "./pages/DocumentsUploadPage";
import ReportConfirmContactDetailsPage from "./pages/ReportConfirmContactDetailsPage";
import ReportDeclarationPage from "./pages/ReportDeclarationPage";
import ReportOverviewPage from "./pages/ReportOverviewPage";
import ReportReviewPage from "./pages/ReportReviewPage";
import { loginAsAdmin, loginAsDeputy } from "./session/factory";

const deputyReference = "document-synchronisation-user";

test("admin dashboard shows correct statuses for pending and synchronised documents", async ({
  page,
}) => {
  const runTest = async (scenario: Scenario) => {
    const reportId = scenario.orders[0].reports[0].id;
    const email = scenario.users[deputyReference].email;
    const caseNumber = scenario.orders[0].caseNumber;

    const deputyPage = (await loginAsDeputy(page, email, testPassword)).page;

    // append a document to the report
    const documentsUploadPage = new DocumentsUploadPage(deputyPage, reportId);
    await documentsUploadPage.goto();
    await documentsUploadPage.attachFiles(
      path.join(__dirname, `/testFiles/testimage1.png`),
    );
    await documentsUploadPage.continue();

    // go to report overview in readiness to submit the report
    const reportOverviewPage = new ReportOverviewPage(deputyPage, reportId);
    await reportOverviewPage.goto();
    await reportOverviewPage.previewAndCheckReport();

    // on report preview page, click to confirm contact details
    const reportReviewPage = new ReportReviewPage(deputyPage, reportId);
    await reportReviewPage.isExpected();
    await reportReviewPage.confirmContactDetails();

    // check contact details, click to continue to declaration
    const reportConfirmContactDetailsPage = new ReportConfirmContactDetailsPage(
      deputyPage,
      reportId,
    );
    await reportConfirmContactDetailsPage.isExpected();
    await reportConfirmContactDetailsPage.continueToDeclaration();

    // submit the report
    const reportDeclarationPage = new ReportDeclarationPage(
      deputyPage,
      reportId,
    );
    await reportDeclarationPage.isExpected();
    await reportDeclarationPage.submitReport();

    // check documents in admin UI
    const adminUser = getAdminUserFixture();
    const adminPage = (
      await loginAsAdmin(page, adminUser.email, adminUser.password)
    ).page;

    const adminDocumentsListPage = new AdminDocumentsListPage(adminPage);
    await adminDocumentsListPage.goto();
    await adminDocumentsListPage.openPendingTab();
    await adminDocumentsListPage.search(caseNumber);
    await adminDocumentsListPage.expectDocumentsWithStatuses(
      { name: /testimage1\.png/, status: "Queued" },
      { name: new RegExp(`DigiRep-.+_${caseNumber}\.pdf`), status: "Queued" },
    );

    // submit another document on the already-submitted report
    await documentsUploadPage.goto();
    await documentsUploadPage.attachFiles(
      path.join(__dirname, `/testFiles/testimage2.png`),
    );
    await documentsUploadPage.sendDocuments();

    // check newly-uploaded document is also queued
    await adminDocumentsListPage.goto();
    await adminDocumentsListPage.openPendingTab();
    await adminDocumentsListPage.search(caseNumber);
    await adminDocumentsListPage.expectDocumentsWithStatuses(
      { name: /testimage1\.png/, status: "Queued" },
      { name: /testimage2\.png/, status: "Queued" },
      { name: new RegExp(`DigiRep-.+_${caseNumber}\.pdf`), status: "Queued" },
    );
  };

  // create a single unsubmitted, but ready to submit, report
  await setupFixture(
    createFixtureViaApi("/fixtures/scenarios/layreadytosubmit", {
      deputyReference: deputyReference,
    }),
  ).then((fixture) => runTest(fixture.data as Scenario));
});
