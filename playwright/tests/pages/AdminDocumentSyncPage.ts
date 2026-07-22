import { Page } from "@playwright/test";
import { getAdminURL } from "../fixtures/fixtures";

export default class AdminDocumentSyncPage {
  constructor(private page: Page) {}

  async goto(): Promise<void> {
    await this.page.goto(
      getAdminURL() + "/admin/behat/run-document-sync-command",
    );
  }
}
