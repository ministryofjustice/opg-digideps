import { expect, Page } from "@playwright/test"

/**
 * <FRONT_URL>/report/<reportId>/confirm-details
 *
 * page for confirming contact details during report submission
 */
export default class ReportConfirmContactDetailsPage {
  constructor(private page: Page, private reportId: number) {
  }

  async isExpected() {
    await expect(this.page).toHaveURL(`/report/${this.reportId}/confirm-details`)
  }

  async continueToDeclaration() {
    await this.page.getByRole("link", { name: "Continue to declaration" }).click()
  }
}
