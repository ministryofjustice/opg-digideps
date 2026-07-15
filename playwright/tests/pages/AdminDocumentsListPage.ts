import { expect, Page } from "@playwright/test"
import { getAdminURL } from "../fixtures/fixtures"

/**
 * <ADMIN_URL>/admin/documents/list
 */
export default class AdminDocumentsListPage {
  constructor(protected page: Page) {
  }

  async goto(): Promise<void> {
    await this.page.goto(getAdminURL() + "/admin/documents/list")
  }

  async openPendingTab(): Promise<void> {
    await this.page.getByRole("tab", { name: "Pending" }).click()
  }

  async openSynchronisedTab() {
    await this.page.getByRole("tab", { name: "Synchronised" }).click()
  }

  async search(query: string): Promise<void> {
    await this.page.locator("#search").fill(query)
    await this.page.locator("#search_submit").click()
  }

  // check that the provided document file names appear in the search results
  // with corresponding statuses
  async expectDocumentsWithStatuses(...expectedFileNamesAndStatuses: Array<{ name: RegExp, status: string }>): Promise<void> {
    const selector = 'tr[data-role="report-submission-documents"] tbody tr'
    const documentSubmissions = await this.page.locator(selector).all()

    const fileNames = []
    let asExpected = 0
    for (const documentSubmission of documentSubmissions) {
      let fileName = await documentSubmission.locator("td").nth(0).textContent()
      let status = await documentSubmission.locator("td").nth(2).textContent()

      if (fileName !== null && status !== null) {
        fileName = fileName.trim()
        status = status.trim()

        fileNames.push(fileName)

        for (const { name: expectedRegExp, status: expectedStatus } of expectedFileNamesAndStatuses) {
          if (expectedRegExp.exec(fileName) && status === expectedStatus) {
            asExpected++
            break;
          }
        }
      }
    }

    expect(asExpected).toBe(expectedFileNamesAndStatuses.length)
    expect(documentSubmissions.length).toBe(expectedFileNamesAndStatuses.length)
  }
}
