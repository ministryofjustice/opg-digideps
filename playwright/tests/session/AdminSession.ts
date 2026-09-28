import type { Page } from "@playwright/test";

export default class AdminSession {
  constructor(public readonly page: Page) {}
}
