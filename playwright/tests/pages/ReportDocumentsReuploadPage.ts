import { expect, Page } from "@playwright/test"

/**
 * <FRONT_URL>/report/<reportId>/documents/reupload
 *
 * page for re-uploading documents whose S3 object has gone
 */
export default class ReportDocumentsReuploadPage {
  constructor(private page: Page, private reportId: number) {
  }

  async isExpected() {
    await expect(this.page).toHaveURL(`/report/${this.reportId}/documents/reupload`)
  }

  async removeDocument(fileName:string) {
    await this.page.getByRole('link', { name: 'Remove ' + fileName }).click()
  }

  async uploadFiles(...filePaths: Array<string>) {
    const elementLocator = "#report_document_upload_files"

    await this.page.locator(elementLocator).setInputFiles(filePaths)

    // synthesise a change event on the file input (this doesn't appear
    // to be triggered by setInputFiles())
    await this.page.locator(elementLocator).evaluate((element) =>
      element.dispatchEvent(new Event('change', { bubbles: true }))
    )

    // press the upload button
    await this.page.getByRole('button', { name: 'Upload' }).click()
  }

  async saveAndContinue() {
    await this.page.getByRole('link', { name: 'Save and continue' }).click()
  }
}
