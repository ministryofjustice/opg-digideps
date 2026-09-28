import type { Page } from "@playwright/test";

export default class AdminSession {
  constructor(public readonly page: Page) {}

  public async close(): Promise<void> {
    await this.page.context().close();
  }
}
