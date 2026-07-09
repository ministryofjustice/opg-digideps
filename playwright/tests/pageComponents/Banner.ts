// checks text in the flash message banner
import { expect, Page } from "@playwright/test";

export default class Banner {
  private bannerSuccessSelector: string = "div.moj-banner.moj-banner--success"
  private bannerFailureSelector: string = "div.moj-banner.moj-banner--fail"

  constructor(private page: Page) {}

  async expectSuccessMessage(message: string) {
    const bannerSuccess = this.page.locator(this.bannerSuccessSelector)
    await expect(bannerSuccess).toContainText(message)
  }

  async expectFailureMessage(message: string) {
    const bannerFailure = this.page.locator(this.bannerFailureSelector)
    await expect(bannerFailure).toContainText(message)
  }
}
