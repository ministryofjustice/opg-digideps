import path = require("path");
import { expect, test } from "@playwright/test";
import { createSimpleLay, Scenario, setupScenario, testPassword } from "./fixtures/fixtures";
import AttachDocumentsPage from "./pages/AttachDocumentsPage";
import LoginPage from "./pages/LoginPage";

test("a user sends further documents", async ({ page }) => {
    const deputyReference = "attaching-further-documents-user"

    const runTest = async (scenario: Scenario) => {
      const email = scenario.users[deputyReference].email
      const courtOrderUid = scenario.orders[0].courtOrderUid
      const submittedReportId = scenario.orders[0].reports[0].id

      // login as deputy
      const loginPage = new LoginPage(page)
      await loginPage.goto()
      await loginPage.login({ email: email, password: testPassword })

      // go to the attach documents page
      const attachDocumentsPage = new AttachDocumentsPage(page)

      // attach two files
      let uploadedFiles = []
      for (const fileToUpload of ["testimage1.png", "testimage2.png"]) {
        await attachDocumentsPage.goto(submittedReportId)

        let fileToUploadFullPath = path.join(__dirname, `/testFiles/${fileToUpload}`)
        await attachDocumentsPage.attachFile(fileToUploadFullPath)
        await attachDocumentsPage.sendDocuments()

        // check we're redirected to the court order page with success message
        await expect(page).toHaveURL(`/courtorder/${courtOrderUid}`)
        await expect(page.locator("div.moj-banner--success"))
          .toContainText("Your uploaded files are now attached to this report")

        // check correct files are listed as attachments
        uploadedFiles.push(fileToUpload)

        await attachDocumentsPage.goto(submittedReportId)
        for (const uploadedFile of uploadedFiles) {
          let selector = `dt[data-role="attached-document-name"]:has-text("${uploadedFile}")`
          await expect(page.locator(selector)).toHaveCount(1)
        }
      }
    }

    await setupScenario(createSimpleLay(deputyReference))
      .then(scenario => {
        if (scenario === null) {
          throw new Error("Unable to create scenario for attaching further documents")
        }

        return scenario
      })
      .then(runTest)
})
