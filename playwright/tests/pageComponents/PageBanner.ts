// checks text in the flash message banner
import { expect, Page } from "@playwright/test";

export default class PageBanner {
  private selector: string = "div.moj-banner.moj-banner--success";

  constructor(private page: Page) {}

  async expectSuccessMessage(message: string) {
    const bannerSuccess = this.page.locator(this.selector);
    await expect(bannerSuccess).toContainText(message);
  }
}
