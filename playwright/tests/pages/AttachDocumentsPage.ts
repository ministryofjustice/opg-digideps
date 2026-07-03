import { Page } from "@playwright/test"

export default class AttachDocumentsPage {
  constructor(private page: Page) {}

  async goto(reportId: number) {
    await this.page.goto("/report/" + String(reportId) + "/documents/step/2")
  }

  async attachFile(filePath: string, elementLocator: string) {
    // set up wait for the redirect after the file is uploaded
    const navigationPromise = this.page.waitForURL(/.+\?successUploaded=true/)

    await this.page.locator(elementLocator).setInputFiles(filePath)

    // synthesise a change event on the file input (this doesn't appear
    // to be triggered by setInputFiles())
    await this.page.locator(elementLocator).evaluate((element) =>
      element.dispatchEvent(new Event('change', { bubbles: true }))
    )

    await navigationPromise
  }
}
