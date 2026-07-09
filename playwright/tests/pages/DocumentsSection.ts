import { Page } from "@playwright/test"

export default class DocumentsSection {
  constructor(private page: Page) {
  }

  // press start button
  async start(reportId: number) {
    await this.page.goto("/report/" + String(reportId) + "/documents")
    await this.page.locator(".behat-link-start").click()
  }

  async checkDocumentsToAdd(option: "yes" | "no") {
    await this.page.locator('[name="document[wishToProvideDocumentation]"][value="' + option + '"]').setChecked(true)
    await this.page.getByText("Save and continue").click()
  }
}
