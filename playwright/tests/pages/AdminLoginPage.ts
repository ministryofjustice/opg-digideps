import { getAdminURL } from "../fixtures/fixtures";
import { Page } from "@playwright/test";

/**
 * <ADMIN_URL>/login
 */
export default class AdminLoginPage {
  constructor(protected page: Page) {}

  async loginAdmin(user: { email: string; password: string }) {
    await this.page.goto(getAdminURL() + "/login");

    await this.page.fill("#login_email", user.email);
    await this.page.fill("#login_password", user.password);

    await Promise.all([
      this.page.waitForLoadState("networkidle"),
      this.page.click("#login_login"),
    ]);
  }
}
