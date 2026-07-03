import { test } from "@playwright/test";
import { createSimpleLay, Scenario, setupScenario, testPassword } from "./fixtures/fixtures";
import LoginPage from "./pages/LoginPage";

test("a user attempts to send further documents", async ({ page }) => {
    const deputyReference = "attaching-further-documents-user"

    const runTest = async (scenario: Scenario) => {
      const email = scenario.users[deputyReference].email

      // login as deputy
      const loginPage = new LoginPage(page)
      await loginPage.goto()
      await loginPage.login({ email: email, password: testPassword })
    }

    await setupScenario(createSimpleLay(deputyReference))
      .then(runTest)
})
