import { getAdminURL, TestUser } from "../fixtures/fixtures";
import { Page } from "@playwright/test";

/**
 * <ADMIN_URL>/login
 */
export default class AdminLoginPage {
  constructor(protected page: Page) {}

  async loginAdmin(user: { email: string; password: string } | TestUser) {
    await this.page.goto(getAdminURL() + "/login");

    await this.page.fill("#login_email", user.email);
    await this.page.fill("#login_password", user.password);

    await this.page.click("#login_login");
    await this.page.waitForLoadState("networkidle");
  }
}
