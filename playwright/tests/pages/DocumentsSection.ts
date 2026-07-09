import { Page } from "@playwright/test"
import AttachDocumentsPage from "./AttachDocumentsPage";

export default class DocumentsSection {
  private attachDocumentsPage: AttachDocumentsPage;

  constructor(private page: Page, private reportId: number) {
    this.attachDocumentsPage = new AttachDocumentsPage(page, this.reportId)
  }

  async goto() {
    await this.page.goto("/report/" + String(this.reportId) + "/documents")
  }

  // press "Start >" button for documents section
  async start() {
    await this.goto()
    await this.page.locator(".behat-link-start").click()
  }

  async answerDocumentsToAddQuestion(option: "yes" | "no") {
    await this.page.locator('[name="document[wishToProvideDocumentation]"][value="' + option + '"]').setChecked(true)
    await this.page.getByText("Save and continue").click()
  }

  // in this context, we'll be presented with a "Continue" button to submit the files
  async attachFiles(...filePaths: Array<string>) {
    await this.attachDocumentsPage.goto()
    await this.attachDocumentsPage.attachFiles(...filePaths)
    await this.attachDocumentsPage.continue()
  }
}
