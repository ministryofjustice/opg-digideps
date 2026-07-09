import path = require("path")
import { Page, test } from "@playwright/test";
import { createSimpleLay, Scenario, setupScenario, testPassword } from "./fixtures/fixtures"
import DocumentsSection from "./pages/DocumentsSection"
import LoginPage from "./pages/LoginPage"
import ReportOverviewPage from "./pages/ReportOverviewPage"
import AttachDocumentsPage from "./pages/AttachDocumentsPage";

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

const setupScenarioAndRunTest = (runTest: (scenario: Scenario) => Promise<void>) => {
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
    const reportId = scenario.orders[0].reports[1].id
    const email = scenario.users[deputyReference].email

    await startDocumentsSection(page, email, reportId, "no")

    // check "No documents" is shown for documents section in report overview
    const reportOverviewPage = new ReportOverviewPage(page, reportId)
    await reportOverviewPage.goto()
    await reportOverviewPage.expectSectionStatus("Supporting documents", "No documents")
  }

  await setupScenarioAndRunTest(runTest)
})

test("a user uploads multiple supporting documents with valid file types", async ({ page }) => {
  const runTest = async (scenario: Scenario): Promise<void> => {
    const reportId = scenario.orders[0].reports[1].id
    const email = scenario.users[deputyReference].email

    await startDocumentsSection(page, email, reportId, "yes")

    const attachDocumentsPage = new AttachDocumentsPage(page, reportId)
    await attachDocumentsPage.attachFiles(path.join(__dirname, "/testFiles/testimage1.png"))

    // check "1 document" is shown for documents section in report overview
    const reportOverviewPage = new ReportOverviewPage(page, reportId)
    await reportOverviewPage.goto()
    await reportOverviewPage.expectSectionStatus("Supporting documents", "1 document")

    // attach another document, check section status again
    await attachDocumentsPage.attachFiles(path.join(__dirname, "/testFiles/testimage2.png"))
    await reportOverviewPage.goto()
    await reportOverviewPage.expectSectionStatus("Supporting documents", "2 documents")
  }

  await setupScenarioAndRunTest(runTest)
})

test("a user uploads multiple supporting documents with valid file types which require conversion", async ({ page }) => {
  const runTest = async (scenario: Scenario): Promise<void> => {
    const reportId = scenario.orders[0].reports[1].id
    const email = scenario.users[deputyReference].email

    await startDocumentsSection(page, email, reportId, "yes")

    const attachDocumentsPage = new AttachDocumentsPage(page, reportId)
    await attachDocumentsPage.attachFiles(
      path.join(__dirname, "/testFiles/goodheic.heic"),
      path.join(__dirname, "/testFiles/goodjfif.jfif")
    )

    // check file suffices in the document summary page have been converted
    await attachDocumentsPage.expectFileNames(["goodjfif.jpeg", "goodheic.jpeg"])

    // check "2 documents" is shown for documents section in report overview
    const reportOverviewPage = new ReportOverviewPage(page, reportId)
    await reportOverviewPage.goto()
    await reportOverviewPage.expectSectionStatus("Supporting documents", "2 documents")
  }

  await setupScenarioAndRunTest(runTest)
})
