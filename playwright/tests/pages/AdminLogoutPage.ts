import { Page } from "@playwright/test";
import { getAdminURL } from "../fixtures/fixtures";

/**
 * <ADMIN_URL>/logout
 */
export default class AdminLogoutPage {
  constructor(private page: Page) {}

  async goto() {
    await this.page.goto(getAdminURL() + "/logout");
  }
}
