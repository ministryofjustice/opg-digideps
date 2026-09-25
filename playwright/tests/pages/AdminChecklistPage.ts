import { getAdminURL } from "../fixtures/fixtures";
import { Page } from "@playwright/test";

/**
 * <ADMIN_URL>/admin/report/<reportId>/checklist
 */
export default class AdminChecklistPage {
  private readonly url: string;

  constructor(
    private page: Page,
    private reportId: number,
  ) {
    this.url =
      getAdminURL() + `/admin/report/${String(this.reportId)}/checklist`;
  }

  async goto() {
    await this.page.goto(this.url);
  }

  async markOPG103SectionsSatisfactory() {
    await this.page
      .getByRole("group", { name: "L1.1 Is the reporting period correct" })
      .getByLabel("Yes")
      .check();

    await this.page
      .getByRole("checkbox", { name: "Contact details are correct" })
      .check();
    await this.page
      .getByRole("checkbox", {
        name: "Deputy's full name on Sirius is correct",
      })
      .check();

    await this.page
      .getByRole("group", {
        name: `L2.1 Have satisfactory responses been provided`,
      })
      .getByLabel("Yes")
      .check();

    await this.page
      .getByRole("group", {
        name: "L3.1 Have satisfactory responses been provided",
      })
      .getByLabel("Yes")
      .check();

    await this.page
      .getByRole("group", {
        name: "L4.1 Have satisfactory responses been provided",
      })
      .getByLabel("Yes")
      .check();

    await this.page
      .getByRole("group", { name: "L6.1 Are you satisfied" })
      .getByLabel("Yes")
      .check();

    await this.page
      .getByRole("group", { name: "L6.2 If the client has any debt" })
      .getByLabel("Yes")
      .check();

    // L7.1
    await this.page
      .locator("#report_checklist_clientBenefitsChecked_1")
      .check();

    // L8.1
    await this.page
      .locator("#report_checklist_openClosingBalancesMatch_0")
      .check();

    // L8.3
    await this.page
      .locator("#report_checklist_moneyMovementsAcceptable_0")
      .check();

    // L9.1
    await this.page.locator("#report_checklist_bondAdequate_0").check();

    // L9.2
    await this.page.locator("#report_checklist_bondOrderMatchSirius_0").check();

    // L13.1
    await this.page
      .locator("#report_checklist_futureSignificantDecisions_0")
      .check();

    // L13.2
    await this.page
      .locator("#report_checklist_hasDeputyRaisedConcerns_1")
      .check();

    await this.page
      .getByRole("textbox", { name: "Lodging summary concerns" })
      .fill("no concerns");

    // L14.1
    await this.page.locator("#report_checklist_caseWorkerSatisified_0").check();

    await this.page.getByRole("radio", { name: "I am satisfied" }).check();
  }

  async submitChecklistAndContinue() {
    await this.page
      .getByRole("button", { name: "Submit checklist and continue" })
      .click();
  }

  async getSubmissionDetails() {
    const syncStatus = await this.page
      .locator("[data-role='checklist-sync-status']")
      .textContent();

    const submittedBy = await this.page
      .locator("[data-role='checklist-submitted-by']")
      .textContent();

    const submittedOn = await this.page
      .locator("[data-role='checklist-submitted-on']")
      .textContent();

    return Promise.resolve({
      syncStatus: syncStatus,
      submittedBy: submittedBy,
      submittedOn: submittedOn,
    });
  }
}
