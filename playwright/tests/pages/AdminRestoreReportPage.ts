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

  async expectConfirmation(details: {
    courtOrderReferences: string;
    clientName: string;
    reportType: string;
    period: string;
  }) {
    await expect(
      this.page.getByRole("heading", { name: "Report details", level: 2 }),
    ).toBeVisible();
    for (const line of [
      `Court order reference number(s): ${details.courtOrderReferences}`,
      `Name of client: ${details.clientName}`,
      `Type of report: ${details.reportType}`,
      `Reporting period: ${details.period}`,
      "Please note:",
    ]) {
      await expect(this.page.getByText(line, { exact: true })).toBeVisible();
    }
    await expect(
      this.page.locator("ul.govuk-list--bullet").getByRole("listitem"),
    ).toHaveText([
      "restoring this report will reinstate all the information previously entered by the deputy in their unsubmitted report",
      "you must have explained to the deputy that the information can then be viewed and edited by all deputies on the active court order for this report",
      "if the report type on the active court order is different from the unsubmitted report only shared sections will be restored",
      "the restored report type will be updated to match the type in the active court order",
    ]);
    const buttonGroup = this.page.locator(".govuk-button-group");
    await expect(
      buttonGroup.getByRole("button", { name: "Confirm" }),
    ).toBeVisible();
    await expect(
      buttonGroup.getByRole("link", { name: "Cancel", exact: true }),
    ).toHaveAttribute("href", `/admin/client/${String(this.clientId)}/details`);
  }

  async confirmRestore() {
    await this.page
      .getByRole("button", { name: "Confirm", exact: true })
      .click();
  }
}
