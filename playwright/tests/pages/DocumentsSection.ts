import { Page } from "@playwright/test"

export default class DocumentsSection {
  constructor(private page: Page) {
  }

  // press start button
  async start(reportId: number) {
    await this.page.goto("/report/" + String(reportId) + "/documents")
    await this.page.locator(".behat-link-start").click()
  }
}
