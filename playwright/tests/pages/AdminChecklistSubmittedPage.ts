import { getAdminURL } from "../fixtures/fixtures";
import { Page } from "@playwright/test";

/**
 * <ADMIN_URL>/admin/report/<reportId>/checklist-submitted
 */
export default class AdminChecklistSubmittedPage {
  private readonly url: string;

  constructor(
    private page: Page,
    private reportId: number,
  ) {
    this.url = getAdminURL() + `/admin/report/${String(this.reportId)}/checklist-submitted`
  }

  async goto() {
    await this.page.goto(this.url);
  }
}
