import { getAdminURL } from "../fixtures/fixtures";
import { Page } from "@playwright/test";

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
}
