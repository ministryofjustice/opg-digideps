import { expect, Page } from "@playwright/test";

export default class ReportOverviewPage {
  private sectionStatuses: Record<string, string> = {}

  constructor(private page: Page) {
  }

  private async getSectionStatuses() {
    if (Object.keys(this.sectionStatuses).length === 0) {
      const sections = await this.page.locator('[data-role="report-overview-subsection"]').all()

      console.log(sections)

      for (const section of sections) {
        let sectionName = await section.locator('[data-role="report-overview-subsection-name"]').textContent()
        let sectionStatus = await section.locator('[data-role="report-overview-subsection-status"]').textContent()

        if (sectionName !== null && sectionStatus !== null) {
          this.sectionStatuses[sectionName.trim()] = sectionStatus.trim()
        }
      }
    }

    return this.sectionStatuses
  }

  async goto(reportId: number) {
    await this.page.goto("/report/" + String(reportId) + "/overview")
  }

  async expectSectionStatus(section: string, status: string) {
    const sectionStatuses = await this.getSectionStatuses()
    expect(sectionStatuses[section]).toBe(status)
  }
}
