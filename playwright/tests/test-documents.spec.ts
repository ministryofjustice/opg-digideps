import path = require("path")
import { Page, test } from "@playwright/test";
import { createSimpleLay, Scenario, setupScenario, testPassword } from "./fixtures/fixtures"
import DocumentsIntroPage from "./pages/DocumentsIntroPage"
import LoginPage from "./pages/LoginPage"
import ReportOverviewPage from "./pages/ReportOverviewPage"
import DocumentsUploadPage from "./pages/DocumentsUploadPage";
import DocumentsSummaryPage from "./pages/DocumentsSummaryPage";
import PageBanner from "./pageComponents/PageBanner";
import PageErrorMessage from "./pageComponents/PageErrorMessage";

const deputyReference = "documents-user"

const startDocumentsSection = async (
  page: Page, email: string, reportId: number, documentsToAdd: "yes" | "no"
): Promise<DocumentsIntroPage> => {
  // login as deputy
  const loginPage = new LoginPage(page)
  await loginPage.goto()
  await loginPage.login({ email: email, password: testPassword })

  // start documents section
  const documentsSection = new DocumentsIntroPage(page, reportId)
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

    const documentsUploadPage = new DocumentsUploadPage(page, reportId)
    await documentsUploadPage.attachFiles(path.join(__dirname, "/testFiles/testimage1.png"))

    // check "1 document" is shown for documents section in report overview
    const reportOverviewPage = new ReportOverviewPage(page, reportId)
    await reportOverviewPage.goto()
    await reportOverviewPage.expectSectionStatus("Supporting documents", "1 document")

    // attach another document, check section status again
    await documentsUploadPage.goto()
    await documentsUploadPage.attachFiles(path.join(__dirname, "/testFiles/testimage2.png"))
    await reportOverviewPage.goto()
    await reportOverviewPage.expectSectionStatus("Supporting documents", "2 documents")
  }

  await setupScenarioAndRunTest(runTest)
})

test("a user uploads a file with an invalid file type", async ({ page }) => {
  const runTest = async (scenario: Scenario): Promise<void> => {
    const reportId = scenario.orders[0].reports[1].id
    const email = scenario.users[deputyReference].email

    await startDocumentsSection(page, email, reportId, "yes")

    const documentsUploadPage = new DocumentsUploadPage(page, reportId)
    await documentsUploadPage.attachFiles(path.join(__dirname, "/testFiles/badfiletype.txt"))

    const errors = new PageErrorMessage(page)
    await errors.expectErrorMessage("Please upload a valid file type")
  }

  await setupScenarioAndRunTest(runTest)
})

test("a user uploads multiple supporting documents with valid file types which require conversion", async ({ page }) => {
  const runTest = async (scenario: Scenario): Promise<void> => {
    const reportId = scenario.orders[0].reports[1].id
    const email = scenario.users[deputyReference].email

    await startDocumentsSection(page, email, reportId, "yes")

    const documentsUploadPage = new DocumentsUploadPage(page, reportId)
    await documentsUploadPage.attachFiles(
      path.join(__dirname, "/testFiles/goodheic.heic"),
      path.join(__dirname, "/testFiles/goodjfif.jfif")
    )

    // check file suffices in the document summary page have been converted
    await documentsUploadPage.expectFileNames("goodjfif.jpeg", "goodheic.jpeg")

    // check "2 documents" is shown for documents section in report overview
    const reportOverviewPage = new ReportOverviewPage(page, reportId)
    await reportOverviewPage.goto()
    await reportOverviewPage.expectSectionStatus("Supporting documents", "2 documents")
  }

  await setupScenarioAndRunTest(runTest)
})

test("a user deletes documents", async ({ page }) => {
  const runTest = async (scenario: Scenario): Promise<void> => {
    const reportId = scenario.orders[0].reports[1].id
    const email = scenario.users[deputyReference].email

    await startDocumentsSection(page, email, reportId, "yes")

    const banner = new PageBanner(page)
    const documentsUploadPage = new DocumentsUploadPage(page, reportId)
    await documentsUploadPage.attachFiles(
      path.join(__dirname, "/testFiles/testimage1.png"),
      path.join(__dirname, "/testFiles/testimage2.png"),
      path.join(__dirname, "/testFiles/testimage3.png")
    )

    // delete documents via the documents upload page
    await documentsUploadPage.removeFiles("testimage1.png")

    await banner.expectSuccessMessage("File named testimage1.png has been removed")
    await documentsUploadPage.expectFileNames("testimage2.png", "testimage3.png")

    // delete documents via the documents summary page
    const documentsSummaryPage = new DocumentsSummaryPage(page, reportId)
    await documentsSummaryPage.goto()
    await documentsSummaryPage.removeFiles("testimage2.png")

    await banner.expectSuccessMessage("File named testimage2.png has been removed")
    await documentsSummaryPage.expectFileNames("testimage3.png")
  }

  await setupScenarioAndRunTest(runTest)
})
