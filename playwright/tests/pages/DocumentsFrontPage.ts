import { Page } from "@playwright/test";

/**
 * <FRONT_URL>/reports/<reportId>/documents
 */
export default class DocumentsFrontPage {
  constructor(
    private page: Page,
    private reportId: number,
  ) {}

  async goto() {
    await this.page.goto("/report/" + String(this.reportId) + "/documents");
  }

  // press "Start >" button for documents section
  async start() {
    await this.goto();
    await this.page.locator(".behat-link-start").click();
  }

  async answerDocumentsToAddQuestion(option: "yes" | "no") {
    await this.page
      .locator(
        '[name="document[wishToProvideDocumentation]"][value="' + option + '"]',
      )
      .setChecked(true);
    await this.page.getByText("Save and continue").click();
  }
}
