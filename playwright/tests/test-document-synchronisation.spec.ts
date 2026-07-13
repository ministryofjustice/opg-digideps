import path from "path"
import { test } from "@playwright/test"
import { createScenarioViaApi, getUserFixture, Scenario, setupScenario, testPassword } from "./fixtures/fixtures"
import AdminDocumentsListPage from "./pages/AdminDocumentsListPage"
import AdminLoginPage from "./pages/AdminLoginPage"
import DocumentsUploadPage from "./pages/DocumentsUploadPage"
import LoginPage from "./pages/LoginPage"
import ReportConfirmContactDetailsPage from "./pages/ReportConfirmContactDetailsPage"
import ReportDeclarationPage from "./pages/ReportDeclarationPage"
import ReportOverviewPage from "./pages/ReportOverviewPage"
import ReportReviewPage from "./pages/ReportReviewPage"

const deputyReference = "document-synchronisation-user"

test("submitting a report sets the report PDF document's synchronisation status to queued in the admin dashboard", async ({ page }) => {
  const runTest = async (scenario: Scenario) => {
    const reportId = scenario.orders[0].reports[0].id
    const email = scenario.users[deputyReference].email
    const caseNumber = scenario.orders[0].caseNumber

    const loginPage = new LoginPage(page)
    await loginPage.goto()
    await loginPage.login({ email: email, password: testPassword })

    // append a document to the report
    const documentsUploadPage = new DocumentsUploadPage(page, reportId)
    await documentsUploadPage.goto()
    await documentsUploadPage.attachFiles(path.join(__dirname, `/testFiles/testimage1.png`))
    await documentsUploadPage.continue()

    // go to report overview in readiness to submit the report
    const reportOverviewPage = new ReportOverviewPage(page, reportId)
    await reportOverviewPage.goto()
    await reportOverviewPage.previewAndCheckReport()

    // on report preview page, click to confirm contact details
    const reportReviewPage = new ReportReviewPage(page, reportId)
    await reportReviewPage.isExpected()
    await reportReviewPage.confirmContactDetails()

    // check contact details, click to continue to declaration
    const reportConfirmContactDetailsPage = new ReportConfirmContactDetailsPage(page, reportId)
    await reportConfirmContactDetailsPage.isExpected()
    await reportConfirmContactDetailsPage.continueToDeclaration()

    // submit the report
    const reportDeclarationPage = new ReportDeclarationPage(page, reportId)
    await reportDeclarationPage.isExpected()
    await reportDeclarationPage.submitReport()

    // check the report document's status in admin dashboard
    const adminLoginPage = new AdminLoginPage(page)
    await adminLoginPage.loginAdmin(getUserFixture("admin_user"))

    // check documents in admin UI
    const adminClientSearchPage = new AdminDocumentsListPage(page)
    await adminClientSearchPage.goto()
    await adminClientSearchPage.openPendingTab()
    await adminClientSearchPage.search(caseNumber)
    await adminClientSearchPage.expectDocumentsWithStatuses(
      { name: /testimage1\.png/, status: "Queued" },
      { name: new RegExp(`DigiRep-.+_${caseNumber}\.pdf`), status: "Queued" }
    )
  }

  // create a single unsubmitted, but ready to submit, report
  await setupScenario(
    createScenarioViaApi("/fixtures/scenarios/layreadytosubmit", {deputyReference: deputyReference})
  )
  .then(runTest)
})

test("submitting a supporting document after a report submission sets that document's synchronisation status to queued in the admin dashboard", async ({ page }) => {
})

test("running the document sync command synchronises queued documents with Sirius", async ({ page }) => {
})
