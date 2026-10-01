import { expect, Page } from "@playwright/test";

/**
 * <FRONT_URL>/report/<reportId>/overview
 */
export default class ReportOverviewPage {
  private readonly url: string;

  constructor(
    private page: Page,
    private reportId: number,
  ) {
    this.url = `/report/${String(this.reportId)}/overview`;
  }

  async goto() {
    await this.page.goto(this.url);
  }

  async isExpected() {
    await expect(this.page).toHaveURL(new RegExp(this.url));
  }

  async expectSectionStatus(section: string, status: string) {
    const sections = await this.page
      .locator('[data-role="report-overview-subsection"]')
      .all();

    const sectionStatuses: Record<string, string> = {};
    for (const section of sections) {
      const sectionName = await section
        .locator('[data-role="report-overview-subsection-name"]')
        .textContent();
      const sectionStatus = await section
        .locator('[data-role="report-overview-subsection-status"]')
        .textContent();

      if (sectionName !== null && sectionStatus !== null) {
        sectionStatuses[sectionName.trim()] = sectionStatus.trim();
      }
    }

    expect(sectionStatuses[section]).toBe(status);
  }

  // press "Preview and check report"
  async previewAndCheckReport() {
    await this.page.locator("a.behat-link-report-submit").click();
  }
}
