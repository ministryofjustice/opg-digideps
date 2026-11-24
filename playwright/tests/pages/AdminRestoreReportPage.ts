import { expect, Page } from "@playwright/test";
import { getAdminURL } from "../fixtures/fixtures";

/**
 * <ADMIN_URL>/admin/report/<replaceableReportId>/restore
 */
export default class AdminRestoreReportPage {
  constructor(
    private page: Page,
    private replaceableReportId: number,
    private restoreFromReportId: number,
    private clientId: number,
  ) {}

  async isExpected() {
    await expect(this.page).toHaveURL(
      getAdminURL() +
        `/admin/report/${String(this.replaceableReportId)}/restore?restoreFromReportId=${String(this.restoreFromReportId)}&clientId=${String(this.clientId)}`,
    );
  }

  async expectConfirmation(reportType: string, period: string) {
    await expect(
      this.page.getByText(
        `You are about to restore the ${reportType} report for ${period}; this will replace all information in the currently active report. All deputies currently active on this case will be able to see and edit this information.`,
        { exact: true },
      ),
    ).toBeVisible();
    await expect(
      this.page.getByText(
        "If the appropriate report type for the active report is different from the report type of the restored report, then only sections shared between these reports will be restored.",
        { exact: true },
      ),
    ).toBeVisible();
    await expect(
      this.page.getByRole("button", { name: "Confirm" }),
    ).toBeVisible();
  }

  async confirmRestore() {
    await this.page
      .getByRole("button", { name: "Confirm", exact: true })
      .click();
  }
}
