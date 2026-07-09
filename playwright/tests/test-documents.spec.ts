import path = require("path")
import { Page, test } from "@playwright/test"
import { createSimpleLay, Scenario, setupScenario, testPassword } from "./fixtures/fixtures"
import DocumentsSection from "./pages/DocumentsSection"
import LoginPage from "./pages/LoginPage"
import ReportOverviewPage from "./pages/ReportOverviewPage"

const deputyReference = "documents-user"

const startDocumentsSection = async (
  page: Page, email: string, reportId: number, documentsToAdd: "yes" | "no"
): Promise<DocumentsSection> => {
  // login as deputy
  const loginPage = new LoginPage(page)
  await loginPage.goto()
  await loginPage.login({ email: email, password: testPassword })

  // start documents section
  const documentsSection = new DocumentsSection(page, reportId)
  await documentsSection.start()
  await documentsSection.answerDocumentsToAddQuestion(documentsToAdd)

  return documentsSection
}

const setupAndRunTest = (runTest: (scenario: Scenario) => Promise<void>) => {
  return setupScenario(createSimpleLay(deputyReference))
    .then(scenario => {
      if (scenario === null) {
        throw new Error("Unable to create scenario for attaching further documents");
      }

      return scenario
    })
    .then(runTest)
}

test("a user has no supporting documents to add", async ({ page }) => {
  const runTest = async (scenario: Scenario): Promise<void> => {
    // use reports[1], as we don't want the submitted report, we want the current one
    const reportId = scenario.orders[0].reports[1].id
    const email = scenario.users[deputyReference].email

    await startDocumentsSection(page, email, reportId, "no")

    // check "No documents" is shown for documents section in report overview
    const reportOverviewPage = new ReportOverviewPage(page)
    await reportOverviewPage.goto(reportId)
    await reportOverviewPage.expectSectionStatus("Supporting documents", "No documents")
  }

  await setupAndRunTest(runTest)
})

test("a user uploads one supporting document with a valid file type", async ({ page }) => {
  const runTest = async (scenario: Scenario): Promise<void> => {
    // use reports[1], as we don't want the submitted report, we want the current one
    const reportId = scenario.orders[0].reports[1].id
    const email = scenario.users[deputyReference].email

    const documentsSection = await startDocumentsSection(page, email, reportId, "yes")
    await documentsSection.attachFiles(path.join(__dirname, "/testFiles/testimage1.png"))

    // check "1 document" is shown for documents section in report overview
    const reportOverviewPage = new ReportOverviewPage(page)
    await reportOverviewPage.goto(reportId)
    await reportOverviewPage.expectSectionStatus("Supporting documents", "1 document")
  }

  await setupAndRunTest(runTest)
})
