import { getAdminURL } from "../fixtures/fixtures";
import { expect, Page } from "@playwright/test";

/**
 * <ADMIN_URL>/admin/client/<clientId>/details
 */
export default class AdminClientDetailsPage {
  private readonly url: string;

  constructor(
    private page: Page,
    private clientId: number,
  ) {
    this.url = getAdminURL() + `/admin/client/${String(this.clientId)}/details`;
  }

  async goto() {
    await this.page.goto(this.url);
  }

  async expectReportsTable(
    caption: string,
    period: string,
    countPeriod: number = 2,
  ) {
    const table = this.page.locator("table.govuk-table").filter({
      has: this.page.locator("caption").filter({ hasText: caption }),
    });

    await expect(table).toHaveCount(1);

    const periodCell = table.locator("td").filter({ hasText: period });

    // NB there is a visually hidden cell which contains the period, as
    // well as the visible "Period" cell
    await expect(periodCell).toHaveCount(countPeriod);
  }
}
