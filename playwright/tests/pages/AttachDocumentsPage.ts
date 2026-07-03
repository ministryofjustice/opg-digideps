import { Page } from "@playwright/test"

export default class AttachDocumentsPage {
  constructor(private page: Page) {}

  async goto(reportId: number) {
    await this.page.goto("/report/" + String(reportId) + "/documents/step/2")
  }
}
