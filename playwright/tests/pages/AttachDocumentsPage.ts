import { expect, Page } from "@playwright/test";

export default class AttachDocumentsPage {
  constructor(private page: Page, private reportId: number) {}

  async goto() {
    await this.page.goto("/report/" + String(this.reportId) + "/documents/step/2")
  }

  async attachFiles(...filePaths: Array<string>) {
    const elementLocator = "#report_document_upload_files"

    // set up wait for the redirect after the file is uploaded
    const navigationPromise = this.page.waitForURL(/.+\?successUploaded=true/)

    await this.page.locator(elementLocator).setInputFiles(filePaths)

    // synthesise a change event on the file input (this doesn't appear
    // to be triggered by setInputFiles())
    await this.page.locator(elementLocator).evaluate((element) =>
      element.dispatchEvent(new Event('change', { bubbles: true }))
    )

    await navigationPromise
  }

  // press "Send documents"
  async sendDocuments() {
    await this.page.locator('a[data-role="send-documents-link"]').click()
  }

  // press "Continue"
  async continue() {
    await this.page.locator("a.behat-link-continue").click()
  }

  async expectFileNames(expectedFileNames: string[]) {
    const selector = "dl.behat-region-document-list > div.govuk-summary-list__row > dt.govuk-summary-list__value"
    const fileNameElements = await this.page.locator(selector).all()

    let fileNames: string[] = []
    for (const fileNameElement of fileNameElements) {
      let text = await fileNameElement.textContent()
      if (text !== null) {
        fileNames.push(text.trim())
      }
    }

    expect(fileNames).toEqual(expect.arrayContaining(expectedFileNames))
  }
}
