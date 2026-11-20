import { expect, Page } from "@playwright/test";
import { getAdminURL } from "../fixtures/fixtures";

/**
 * <ADMIN_URL>/admin/report/<replaceableReportId>/restore
 */
export default class AdminRestoreReportPage {
  constructor(
    private page: Page,
    private replaceableReportId: number,
    private restoreFromReportId: number
  ) {}

  async isExpected() {
    await expect(this.page).toHaveURL(
      getAdminURL() + `/admin/report/${String(this.replaceableReportId)}/restore?restoreFromReportId=${String(this.restoreFromReportId)}`
    );
  }
}
