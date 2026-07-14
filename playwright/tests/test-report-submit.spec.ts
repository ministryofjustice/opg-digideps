import path from "path"
import { test } from "@playwright/test"
import {
  createScenarioViaApi,
  Scenario,
  setupScenario,
  testPassword
} from "./fixtures/fixtures"
import LoginPage from "./pages/LoginPage"
import ReportOverviewPage from "./pages/ReportOverviewPage"
import ReportDocumentsReuploadPage from "./pages/ReportDocumentsReuploadPage"
import PageErrorMessage from "./pageComponents/PageErrorMessage"

const deputyReference = "document-report-submit-user"
const supportingDocument = "testimage1.png"

test("if a document's S3 object is gone, user is redirected to re-upload it on submitting report", async ({ page }) => {
  const runTest = async (scenario: Scenario) => {
    const reportId = scenario.orders[0].reports[0].id
    const email = scenario.users[deputyReference].email

    const loginPage = new LoginPage(page)
    await loginPage.goto()
    await loginPage.login({ email: email, password: testPassword })

    // go to report overview in readiness to submit the report
    const reportOverviewPage = new ReportOverviewPage(page, reportId)
    await reportOverviewPage.goto()
    await reportOverviewPage.previewAndCheckReport()

    // user is shown an error as the backing S3 object has expired
    const reportDocumentsReuploadPage = new ReportDocumentsReuploadPage(page, reportId)
    await reportDocumentsReuploadPage.isExpected()

    const errors = new PageErrorMessage(page)
    await errors.expectErrorMessage("Some of your documents have now expired")

    // remove the document
    await reportDocumentsReuploadPage.removeDocument(supportingDocument)

    // re-attach the document
    await reportDocumentsReuploadPage.uploadFiles(path.join(__dirname, `/testFiles/${supportingDocument}`))
    await reportDocumentsReuploadPage.saveAndContinue()

    // should now be redirected to report overview page
    await reportOverviewPage.isExpected()
  }

  // create a single unsubmitted, but ready to submit, report, with a document
  // that doesn't have a corresponding S3 object
  await setupScenario(
    createScenarioViaApi(
      "/fixtures/scenarios/layreadytosubmit/expireds3objects",
      { deputyReference: deputyReference, supportingDocumentNames: [supportingDocument] }
    )
  ).then(runTest)
})
