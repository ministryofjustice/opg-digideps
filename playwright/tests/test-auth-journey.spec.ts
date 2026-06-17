import { test, expect } from "@playwright/test";
import { createFixture } from "./fixtures/fixtures";
import { LoginPage } from "./pages/login-page";

test("lay user can login", async ({ page }) => {
  const user = createFixture("lay_user");

  const login = new LoginPage(page);

  await login.goto();
  await login.login(user.email, user.password);

  await login.expectOnPage("courtorder");

  await page.goto("/logout");
  await expect(page).toHaveURL(/\/login/);
});

test("org user can login", async ({ page }) => {
  const user = createFixture("pro_user");

  const login = new LoginPage(page);

  await login.goto();
  await login.login(user.email, user.password);

  await login.expectOnPage("org");

  await page.goto("/logout");
  await expect(page).toHaveURL(/\/login/);
});
