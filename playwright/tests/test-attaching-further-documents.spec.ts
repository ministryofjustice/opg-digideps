import path = require("path")
import { expect, test } from "@playwright/test"
import { createScenarioViaApi, getUserFixture, Scenario, setupScenario, testPassword } from "./fixtures/fixtures"
import AdminLoginPage from "./pages/AdminLoginPage"
import DocumentsUploadPage from "./pages/DocumentsUploadPage"
import LoginPage from "./pages/LoginPage"

test("a user sends further documents", async ({ page }) => {
    const deputyReference = "attaching-further-documents-user"

    const runTest = async (scenario: Scenario) => {
      const email = scenario.users[deputyReference].email
      const courtOrderUid = scenario.orders[0].courtOrderUid
      const clientCaseNumber = scenario.orders[0].caseNumber
      const submittedReportId = scenario.orders[0].reports[0].id

      // login as deputy
      const loginPage = new LoginPage(page)
      await loginPage.goto()
      await loginPage.login({ email: email, password: testPassword })

      // go to the attach documents page
      const documentsUploadPage = new DocumentsUploadPage(page, submittedReportId)

      // attach two files
      let uploadedFiles = []
      const filesToUpload = ["testimage1.png", "testimage2.png"]
      for (const fileToUpload of filesToUpload) {
        await documentsUploadPage.goto()

        let fileToUploadFullPath = path.join(__dirname, `/testFiles/${fileToUpload}`)
        await documentsUploadPage.attachFiles(fileToUploadFullPath)
        await documentsUploadPage.sendDocuments()

        // check we're redirected to the court order page with success message
        await expect(page).toHaveURL(`/courtorder/${courtOrderUid}`)
        await expect(page.locator("div.moj-banner--success"))
          .toContainText("Your uploaded files are now attached to this report")

        // check correct files are listed as attachments
        uploadedFiles.push(fileToUpload)

        await documentsUploadPage.goto()
        for (const uploadedFile of uploadedFiles) {
          let selector = `dt[data-role="attached-document-name"]:has-text("${uploadedFile}")`
          await expect(page.locator(selector)).toHaveCount(1)
        }
      }

      // check that reports are shown in admin dashboard
      const adminPage = new AdminLoginPage(page)
      await adminPage.loginAdmin(getUserFixture("admin_user"))

      // go to submissions tag, search for case, click on pending tab,
      // ensure there are two separate file submissions listed, one for each file
      await page.click(".behat-link-admin-documents")
      await page.fill("#search", clientCaseNumber)
      await page.click("#search_submit")
      await page.click(".behat-link-tab-pending")

      const rows = page.locator(".behat-region-report-submission")
      await expect(rows).toHaveCount(2)
      await expect(page.locator(".behat-region-report-submission-documents-1"))
        .toContainText(filesToUpload[0])
      await expect(page.locator(".behat-region-report-submission-documents-2"))
        .toContainText(filesToUpload[1])
    }

    await setupScenario(createScenarioViaApi("/fixtures/scenarios/laysimple", {deputyReference: deputyReference}))
      .then(scenario => {
        if (scenario === null) {
          throw new Error("Unable to create scenario for attaching further documents")
        }

        return scenario
      })
      .then(runTest)
})
