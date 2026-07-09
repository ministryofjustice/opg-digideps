// non-standard error message used on some pages
import { expect, Page } from "@playwright/test";

export default class PageOpgErrorMessage {
  private selector: string = "div.behat-region-alert-message"

  constructor(private page: Page) {}

  async expectErrorMessage(message: string) {
    await expect(this.page.locator(this.selector)).toContainText(message)
  }
}
