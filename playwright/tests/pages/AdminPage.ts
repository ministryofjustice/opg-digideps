import { Page } from "@playwright/test";

export default abstract class AdminPage {
  constructor(protected page: Page) {
  }

  getAdminURL(): string {
    const adminURL = process.env.ADMIN_URL
    if (adminURL === undefined) {
      throw new Error("ADMIN_URL is not set")
    }
    return adminURL
  }
}
