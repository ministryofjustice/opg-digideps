import fs = require("fs")
import os = require("os")
import path = require("path")
import { fail } from "assert"
import { mkdtemp } from "fs/promises"
import { Page, test } from "@playwright/test"
import { createScenarioViaApi, Scenario, setupScenario, testPassword } from "./fixtures/fixtures"
import DocumentsFrontPage from "./pages/DocumentsFrontPage"
import LoginPage from "./pages/LoginPage"
import ReportOverviewPage from "./pages/ReportOverviewPage"
import DocumentsUploadPage from "./pages/DocumentsUploadPage"
import DocumentsSummaryPage from "./pages/DocumentsSummaryPage"
import PageBanner from "./pageComponents/PageBanner"
import PageErrorMessage from "./pageComponents/PageErrorMessage"
import PageOpgErrorMessage from "./pageComponents/PageOpgErrorMessage"

const deputyReference = "documents-user"

const startDocumentsSection = async (
  page: Page, email: string, reportId: number, documentsToAdd: "yes" | "no"
): Promise<DocumentsFrontPage> => {
  // login as deputy
  const loginPage = new LoginPage(page)
  await loginPage.goto()
  await loginPage.login({ email: email, password: testPassword })

  // start documents section
  const documentsFrontPage = new DocumentsFrontPage(page, reportId)
  await documentsFrontPage.start()
  await documentsFrontPage.answerDocumentsToAddQuestion(documentsToAdd)

  return documentsFrontPage
}

const setupScenarioAndRunTest = (runTest: (scenario: Scenario) => Promise<void>) => {
  return setupScenario(createScenarioViaApi("/fixtures/scenarios/laysimple", { deputyReference: deputyReference })).then(runTest)
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

test("a user uploads a file whose suffix does not match its type", async ({ page }) => {
  const runTest = async (scenario: Scenario): Promise<void> => {
    const reportId = scenario.orders[0].reports[1].id
    const email = scenario.users[deputyReference].email

    await startDocumentsSection(page, email, reportId, "yes")

    const documentsUploadPage = new DocumentsUploadPage(page, reportId)
    await documentsUploadPage.attachFiles(path.join(__dirname, "/testFiles/pngfile.jpeg"))

    const errors = new PageErrorMessage(page)
    await errors.expectErrorMessage("Your file type and file extension do not match")
  }

  await setupScenarioAndRunTest(runTest)
})

test("a user uploads a file whose name duplicates an existing upload", async ({ page }) => {
  const runTest = async (scenario: Scenario): Promise<void> => {
    const reportId = scenario.orders[0].reports[1].id
    const email = scenario.users[deputyReference].email

    await startDocumentsSection(page, email, reportId, "yes")

    const documentsUploadPage = new DocumentsUploadPage(page, reportId)
    await documentsUploadPage.attachFiles(path.join(__dirname, "/testFiles/testimage1.png"))
    await documentsUploadPage.attachFiles(path.join(__dirname, "/testFiles/testimage1.png"))

    const errors = new PageErrorMessage(page)
    await errors.expectErrorMessage("You have already uploaded a file with this name")
  }

  await setupScenarioAndRunTest(runTest)
})

test("a user uploads a file which is too large", async ({ page }) => {
  const runTest = async (scenario: Scenario): Promise<void> => {
    const reportId = scenario.orders[0].reports[1].id
    const email = scenario.users[deputyReference].email

    await startDocumentsSection(page, email, reportId, "yes")

    const documentsUploadPage = new DocumentsUploadPage(page, reportId)

    // create a big file in the /tmp directory
    try {
      const tmpDir = await mkdtemp(path.join(os.tmpdir(), "playwright-tests"))
      const tooBigFilePath = path.join(tmpDir, "toobig.png")

      const buffer = Buffer.alloc(16 * 1024 * 1024) // 16MiB
      fs.writeFileSync(tooBigFilePath, buffer)

      await documentsUploadPage.attachFiles(tooBigFilePath)

      fs.rmSync(tooBigFilePath)
    } catch (err) {
      console.error(err);
      fail("Unable to generate file for upload test")
    }

    const errors = new PageErrorMessage(page)
    await errors.expectErrorMessage("The file you selected to upload is too big")
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

test("a user uploads a file and then selects 'no' for supporting documents", async ({ page }) => {
  const runTest = async (scenario: Scenario): Promise<void> => {
    const reportId = scenario.orders[0].reports[1].id
    const email = scenario.users[deputyReference].email

    const documentsFrontPage = await startDocumentsSection(page, email, reportId, "yes")

    const documentsUploadPage = new DocumentsUploadPage(page, reportId)
    await documentsUploadPage.attachFiles(path.join(__dirname, "/testFiles/testimage1.png"))

    const documentsSummaryPage = new DocumentsSummaryPage(page, reportId)
    await documentsSummaryPage.goto()
    await documentsSummaryPage.editSupportingDocumentsAnswer()

    await documentsFrontPage.answerDocumentsToAddQuestion("no")

    const errors = new PageOpgErrorMessage(page)
    await errors.expectErrorMessage("Your answer could not be updated to 'No' because you have attached documents")
  }

  await setupScenarioAndRunTest(runTest)
})
