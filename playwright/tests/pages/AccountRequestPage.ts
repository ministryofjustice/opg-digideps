import { expect, Page } from "@playwright/test";
import { ClientDetails, DeputyDetails } from "../fixtures/fixtures";

/**
 * <FRONT_URL>/account/request
 */
export default class AccountRequestPage {
  private readonly url: string;

  constructor(private page: Page) {
    this.url = "/account/request";
  }

  async goto() {
    await this.page.goto(this.url);
  }

  async isExpected() {
    await expect(this.page).toHaveURL(this.url);
  }

  async enterDeputyDetails(deputyDetails: DeputyDetails) {
    await this.page
      .getByRole("textbox", { name: "Your first name" })
      .fill(deputyDetails.firstName);
    await this.page
      .getByRole("textbox", { name: "Your last name" })
      .fill(deputyDetails.lastName);
    await this.page
      .getByRole("textbox", { name: "Your email address", exact: true })
      .fill(deputyDetails.email);
    await this.page
      .getByRole("textbox", { name: "Confirm your email address", exact: true })
      .fill(deputyDetails.email);
    await this.page
      .getByRole("textbox", { name: "Your post code" })
      .fill(deputyDetails.postCode);
  }

  async enterClientDetails(clientDetails: ClientDetails) {
    await this.page
      .getByRole("textbox", { name: "Client's last name" })
      .fill(clientDetails.lastName);
    await this.page
      .getByRole("textbox", { name: "Case number" })
      .fill(clientDetails.caseNumber);
  }

  async signUp() {
    await this.page.getByRole("button", { name: "Sign up" }).click();
  }

  async expectSuccessMessage() {
    await expect(this.page.getByText("Please check your email")).toBeVisible();
  }

  async expectNotMatchedErrorMessage() {
    await expect(
      this.page.getByText(
        "the information you've given us does not match our records",
      ),
    ).toBeVisible();
  }
}
