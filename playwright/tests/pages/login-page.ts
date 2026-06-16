import { Page, expect } from "@playwright/test";

export class LoginPage {
  constructor(private page: Page, private baseUrl: string) {}

  async goto() {
    await this.page.goto(`${this.baseUrl}/login`);
  }

  async login(email: string, password: string) {
    await this.page.fill("#login_email", email);
    await this.page.fill("#login_password", password);

    await Promise.all([
      this.page.waitForNavigation(),
      this.page.click("#login_login"),
    ]);
  }

  async expectOnPage(expected: string) {
    await expect(this.page).toHaveURL(new RegExp(`/${expected}(/|$)`));
  }
}
