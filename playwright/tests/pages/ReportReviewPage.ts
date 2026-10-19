import { expect, Page } from "@playwright/test";

/**
 * <FRONT_URL>/report/<reportId>/review
 */
export default class ReportReviewPage {
  constructor(
    private page: Page,
    private reportId: number,
  ) {}

  async isExpected() {
    await expect(this.page).toHaveURL(
      `/report/${String(this.reportId)}/review`,
    );
  }

  // press "Confirm contact details"
  async confirmContactDetails() {
    await this.page
      .getByRole("link", { name: "Confirm contact details" })
      .first()
      .click();
  }
}
