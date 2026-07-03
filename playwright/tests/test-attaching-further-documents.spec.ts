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
      await attachDocumentsPage.goto(submittedReportId)

      // attach a file and send it
      const fileToUpload = path.join(__dirname, "/testFiles/test-image.jpg")
      await attachDocumentsPage.attachFile(fileToUpload)
      await attachDocumentsPage.sendDocuments()

      await expect(page).toHaveURL(`/courtorder/${courtOrderUid}`)
      await expect(page.locator("div.moj-banner--success"))
        .toContainText("Your uploaded files are now attached to this report")
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
