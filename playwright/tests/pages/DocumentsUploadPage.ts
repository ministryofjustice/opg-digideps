import DocumentsFileListPage from "./DocumentsFileListPage";

/**
 * <FRONT_URL>/report/<reportId>/documents/step/2
 */
export default class DocumentsUploadPage extends DocumentsFileListPage {
  async goto() {
    await this.page.goto(
      "/report/" + String(this.reportId) + "/documents/step/2",
    );
  }

  async attachFiles(...filePaths: Array<string>) {
    const elementLocator = "#report_document_upload_files";

    // set up wait for the redirect after the file is uploaded
    const navigationPromise = this.page.waitForURL(
      /.+\/report\/\d+\/documents\/step\/2.*$/,
    );

    await this.page.locator(elementLocator).setInputFiles(filePaths);

    // synthesise a change event on the file input (this doesn't appear
    // to be triggered by setInputFiles())
    await this.page
      .locator(elementLocator)
      .evaluate((element) =>
        element.dispatchEvent(new Event("change", { bubbles: true })),
      );

    await navigationPromise;
  }

  // press "Send documents"
  async sendDocuments() {
    await Promise.all([
      this.page.locator('a[data-role="send-documents-link"]').click(),
      this.page.waitForLoadState("networkidle"),
    ]);
  }

  // press "Continue"
  async continue() {
    await Promise.all([
      this.page.locator("a.behat-link-continue").click(),
      this.page.waitForLoadState("networkidle"),
    ]);
  }
}
