import path from "path";
import { test } from "@playwright/test";
import {
  createScenarioViaApi,
  getUserFixture,
  Scenario,
  setupScenario,
  testPassword,
} from "./fixtures/fixtures";
import AdminLoginPage from "./pages/AdminLoginPage";
import DocumentsUploadPage from "./pages/DocumentsUploadPage";
import LoginPage from "./pages/LoginPage";
import AdminDocumentsListPage from "./pages/AdminDocumentsListPage";
import AdminDocumentSyncPage from "./pages/AdminDocumentSyncPage";

test("document-sync command updates document statuses", async ({ page }) => {
  const deputyReference = "attaching-further-documents-user";

  const runTest = async (scenario: Scenario) => {
    // attach documents to submitted report
    const email = scenario.users[deputyReference].email;
    const clientCaseNumber = scenario.orders[0].caseNumber;
    const submittedReportId = scenario.orders[0].reports[0].id;

    // login as deputy
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login({ email: email, password: testPassword });

    // go to the attach documents page and add a document to the submitted report
    const documentsUploadPage = new DocumentsUploadPage(
      page,
      submittedReportId,
    );
    await documentsUploadPage.goto();
    await documentsUploadPage.attachFiles(
      path.join(__dirname, "/testFiles/testimage1.png"),
    );
    await documentsUploadPage.sendDocuments();

    // check pending tab has two documents (report pdf and testimage.png)
    const adminPage = new AdminLoginPage(page);
    await adminPage.loginAdmin(getUserFixture("admin_user"));

    const adminDocumentsListPage = new AdminDocumentsListPage(page);
    await adminDocumentsListPage.goto();
    await adminDocumentsListPage.openPendingTab();
    await adminDocumentsListPage.search(clientCaseNumber);

    // note report is not pending, as it has already been sent
    await adminDocumentsListPage.expectDocumentsWithStatuses(
      { name: /testimage1\.png/, status: "Queued" },
      { name: /DigiRep-.+\.pdf/, status: "Queued" },
    );

    // run the document sync command - this syncs the report PDF
    const adminDocumentSyncPage = new AdminDocumentSyncPage(page);
    await adminDocumentSyncPage.goto();

    // run the document sync command again - this picks up the additional document
    // which has been added to the submitted report
    await adminDocumentSyncPage.goto();

    // check that the files have moved to the synchronised tab
    await adminDocumentsListPage.goto();
    await adminDocumentsListPage.openSynchronisedTab();
    await adminDocumentsListPage.search(clientCaseNumber);
    await adminDocumentsListPage.expectDocumentsWithStatuses(
      { name: /testimage1\.png/, status: "Success" },
      { name: /DigiRep-.+\.pdf/, status: "Success" },
    );

    await page.locator("body").waitFor({ state: "visible" });
  };

  await setupScenario(
    createScenarioViaApi("/fixtures/scenarios/laysimple", {
      deputyReference: deputyReference,
    }),
  ).then(runTest);
});
