// checks text in the page-level error message box
import { expect, Page } from "@playwright/test";

export default class PageErrorMessage {
  private selector: string = "div.govuk-error-summary"

  constructor(private page: Page) {}

  async expectErrorMessage(message: string) {
    const errorMessageElement = await this.page.locator(this.selector).textContent()
    expect(errorMessageElement).toContain(message)
  }
}
