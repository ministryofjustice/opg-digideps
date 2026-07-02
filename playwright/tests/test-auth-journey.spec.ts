import { test, expect } from "@playwright/test";
import { getUserFixture } from "./fixtures/fixtures";
import LoginPage from "./pages/LoginPage";

test("lay user can login", async ({ page }) => {
  const user = getUserFixture("lay_user");

  const login = new LoginPage(page);

  await login.goto();
  await login.login(user);
  await login.expectOnPage("courtorder");

  await page.goto("/logout");
  await expect(page).toHaveURL(/\/login/);
});

test("org user can login", async ({ page }) => {
  const user = getUserFixture("pro_user");

  const login = new LoginPage(page);

  await login.goto();
  await login.login(user);
  await login.expectOnPage("org");

  await page.goto("/logout");
  await expect(page).toHaveURL(/\/login/);
});
