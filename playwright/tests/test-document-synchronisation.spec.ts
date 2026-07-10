import { test } from "@playwright/test";
import { createScenarioViaApi, Scenario, setupScenario, testPassword } from "./fixtures/fixtures";
import LoginPage from "./pages/LoginPage";

const deputyReference = "document-synchronisation-user"

test("submitting a report sets the report PDF document's synchronisation status to queued in the admin dashboard", async ({ page }) => {
  const runTest = async (scenario: Scenario) => {
    const reportId = scenario.orders[0].reports[0].id
    const email = scenario.users[deputyReference].email

    const loginPage = new LoginPage(page)
    await loginPage.goto()
    await loginPage.login({ email: email, password: testPassword })

    // TODO append a document to the report

    // TODO click the submit button and check the report document's status in admin dashboard

    // TODO REMOVE THIS AFTER COMPLETING TESTING
    await page.locator('body').isVisible()
  }

  // single unsubmitted, but ready to submit, report
  await setupScenario(
    createScenarioViaApi("/fixtures/scenarios/layreadytosubmit", {deputyReference: deputyReference})
  )
  .then(runTest)
})

test("submitting a supporting document after a report submission sets that document's synchronisation status to queued in the admin dashboard", async ({ page }) => {
})

test("running the document sync command synchronises queued documents with Sirius", async ({ page }) => {
})
