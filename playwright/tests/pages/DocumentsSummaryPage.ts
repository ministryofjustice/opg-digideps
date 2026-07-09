import DocumentsFileListPage from "./DocumentsFileListPage";

export default class DocumentsSummaryPage extends DocumentsFileListPage{
  async goto() {
    await this.page.goto("/report/" + String(this.reportId) + "/documents/summary")
  }

  // click on the "Edit" button on the summary page which allows the user
  // to change their answer to the "Do you want to provide supporting documents?" question
  async editSupportingDocumentsAnswer() {
    await this.page.locator(".behat-region-provided-documentation a.behat-link-edit").click()
  }
}
