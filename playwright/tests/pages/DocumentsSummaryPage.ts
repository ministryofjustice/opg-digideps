import DocumentsFileListPage from "./DocumentsFileListPage";

export default class DocumentsSummaryPage extends DocumentsFileListPage{
  async goto() {
    await this.page.goto("/report/" + String(this.reportId) + "/documents/summary")
  }
}
