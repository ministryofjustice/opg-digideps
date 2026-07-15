import path from "path";
import { test } from "@playwright/test";
import {
  createScenarioViaApi,
  getUserFixture,
  Scenario,
  setupScenario,
  testPassword,
} from "./fixtures/fixtures";
import AdminDocumentsListPage from "./pages/AdminDocumentsListPage";
import AdminLoginPage from "./pages/AdminLoginPage";
import DocumentsUploadPage from "./pages/DocumentsUploadPage";
import LoginPage from "./pages/LoginPage";
import ReportConfirmContactDetailsPage from "./pages/ReportConfirmContactDetailsPage";
import ReportDeclarationPage from "./pages/ReportDeclarationPage";
import ReportOverviewPage from "./pages/ReportOverviewPage";
import ReportReviewPage from "./pages/ReportReviewPage";

const deputyReference = "document-synchronisation-user";

test("admin dashboard shows correct statuses for pending and synchronised documents", async ({
  page,
}) => {
  const runTest = async (scenario: Scenario) => {
    const reportId = scenario.orders[0].reports[0].id;
    const email = scenario.users[deputyReference].email;
    const caseNumber = scenario.orders[0].caseNumber;

    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login({ email: email, password: testPassword });

    // append a document to the report
    const documentsUploadPage = new DocumentsUploadPage(page, reportId);
    await documentsUploadPage.goto();
    await documentsUploadPage.attachFiles(
      path.join(__dirname, `/testFiles/testimage1.png`),
    );
    await documentsUploadPage.continue();

    // go to report overview in readiness to submit the report
    const reportOverviewPage = new ReportOverviewPage(page, reportId);
    await reportOverviewPage.goto();
    await reportOverviewPage.previewAndCheckReport();

    // on report preview page, click to confirm contact details
    const reportReviewPage = new ReportReviewPage(page, reportId);
    await reportReviewPage.isExpected();
    await reportReviewPage.confirmContactDetails();

    // check contact details, click to continue to declaration
    const reportConfirmContactDetailsPage = new ReportConfirmContactDetailsPage(
      page,
      reportId,
    );
    await reportConfirmContactDetailsPage.isExpected();
    await reportConfirmContactDetailsPage.continueToDeclaration();

    // submit the report
    const reportDeclarationPage = new ReportDeclarationPage(page, reportId);
    await reportDeclarationPage.isExpected();
    await reportDeclarationPage.submitReport();

    // check documents in admin UI
    const adminUser = getUserFixture("admin_user");
    const adminLoginPage = new AdminLoginPage(page);
    await adminLoginPage.loginAdmin(adminUser);

    const adminDocumentsListPage = new AdminDocumentsListPage(page);
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
    await adminLoginPage.loginAdmin(adminUser);

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
  await setupScenario(
    createScenarioViaApi("/fixtures/scenarios/layreadytosubmit", {
      deputyReference: deputyReference,
    }),
  ).then(runTest);
});
