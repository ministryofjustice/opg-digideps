import { test } from "@playwright/test"
import { createSimpleLay, Scenario, setupScenario, testPassword } from "./fixtures/fixtures";
import DocumentsSection from "./pages/DocumentsSection"
import LoginPage from "./pages/LoginPage";

const deputyReference = "documents-user"

test("a user has no supporting documents to add", async ({ page }) => {
  const runTest = async function (scenario: Scenario) {
    const reportId = scenario.orders[0].reports[1].id
    const email = scenario.users[deputyReference].email

    // login as deputy
    const loginPage = new LoginPage(page)
    await loginPage.goto()
    await loginPage.login({ email: email, password: testPassword })

    // start documents section
    const documentsSection = new DocumentsSection(page)
    await documentsSection.start(reportId)
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
