import process = require("process")

import { Page } from "@playwright/test";

/**
 * <ADMIN_URL>/login
 */
export default class AdminLoginPage {
  constructor(private page: Page, private adminURL: string|undefined = process.env.ADMIN_URL) {
    if (this.adminURL === undefined) {
      throw new Error("ADMIN_URL is not set");
    }
  }

  async loginAdmin(user: { email: string; password: string }) {
    await this.page.goto(this.adminURL + "/login");

    await this.page.fill("#login_email", user.email);
    await this.page.fill("#login_password", user.password);

    await Promise.all([
      this.page.waitForLoadState("networkidle"),
      this.page.click("#login_login"),
    ]);
  }
}
