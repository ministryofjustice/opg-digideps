import { expect, Page } from "@playwright/test";

export default class PageOpgBanner {
  // direct child only, as some alert types nest a second opg-alert__message
  private selector: string =
    "div.opg-alert:not(.opg-alert--error) > div.opg-alert__message";

  constructor(private page: Page) {}

  async expectSuccessMessage(message: string) {
    const bannerSuccess = this.page.locator(this.selector);
    await expect(bannerSuccess).toContainText(message);
  }
}
