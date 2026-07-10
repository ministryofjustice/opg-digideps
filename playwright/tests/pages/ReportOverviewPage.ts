import { expect, Page } from "@playwright/test";

/**
 * <FRONT_URL>/report/<reportId>/overview
 */
export default class ReportOverviewPage {
  constructor(private page: Page, private reportId: number) {
  }

  async goto() {
    await this.page.goto("/report/" + String(this.reportId) + "/overview")
  }

  async expectSectionStatus(section: string, status: string) {
    const sections = await this.page.locator('[data-role="report-overview-subsection"]').all()

    let sectionStatuses: Record<string, string> = {}
    for (const section of sections) {
      let sectionName = await section.locator('[data-role="report-overview-subsection-name"]').textContent()
      let sectionStatus = await section.locator('[data-role="report-overview-subsection-status"]').textContent()

      if (sectionName !== null && sectionStatus !== null) {
        sectionStatuses[sectionName.trim()] = sectionStatus.trim()
      }
    }

    expect(sectionStatuses[section]).toBe(status)
  }
}
