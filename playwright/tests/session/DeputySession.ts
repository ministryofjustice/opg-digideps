import type { Page } from "@playwright/test";

export default class DeputySession {
  constructor(public readonly page: Page) {}
}
