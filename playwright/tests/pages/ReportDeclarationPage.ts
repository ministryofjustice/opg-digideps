import { expect, Page } from "@playwright/test"

/**
 * <FRONT_URL>/report/<reportId>/declaration
 *
 * page for confirming contact details during report submission
 */
export default class ReportDeclarationPage {
  constructor(private page: Page, private reportId: number) {
  }

  async isExpected() {
    await expect(this.page).toHaveURL(`/report/${String(this.reportId)}/declaration`)
  }

  // tick checkboxes etc. and submit report;
  // NB this could be broken down later to check specific responses to invalid inputs
  async submitReport() {
    await this.page.getByRole("checkbox", { name: "I agree to this statement" }).check()
    await this.page.getByRole("radio", { name: "I am the only deputy" }).check()
    await this.page.getByRole("button", { name: "Submit report" }).click()
  }
}
