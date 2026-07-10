import { Page, expect } from "@playwright/test";

type ExpectedPage = "courtorder" | "org";

/**
 * <FRONT_URL>/login
 */
export default class LoginPage {
  constructor(private page: Page) {}

  async goto() {
    await this.page.goto("/login");
  }

  async login(user: { email: string; password: string }) {
    await this.page.fill("#login_email", user.email);
    await this.page.fill("#login_password", user.password);

    await Promise.all([
      this.page.waitForLoadState("networkidle"),
      this.page.click("#login_login"),
    ]);
  }

  async expectOnPage(expected: ExpectedPage) {
    await expect(this.page).toHaveURL(new RegExp(`/${expected}(/|$)`));
  }
}
