import { defineConfig } from "@playwright/test";

export default defineConfig({
  testDir: "./tests",
  retries: 1,
  reporter: [["html", { outputFolder: "playwright-report", open: "never" }]],
  use: {
    baseURL: process.env.FRONT_URL,
    ignoreHTTPSErrors: true,
    browserName: "chromium",
    javaScriptEnabled: true,
  },
  workers: 1,
});
