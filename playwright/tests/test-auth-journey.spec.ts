import { test, expect } from "@playwright/test";
import { getUserFixture } from "./fixtures/fixtures";
import LoginPage from "./pages/LoginPage";

test("lay user can login", async ({ page }) => {
  const user = await getUserFixture("Deputy");

  const login = new LoginPage(page);

  await login.goto();
  await login.login(user);
  await login.expectOnPage("client/add");

  await page.goto("/logout");
  await expect(page).toHaveURL(/\/login/);
});

test("org user can login", async ({ page }) => {
  const user = await getUserFixture("Deputy", "PA");

  const login = new LoginPage(page);

  await login.goto();
  await login.login(user);
  await login.expectOnPage("org");

  await page.goto("/logout");
  await expect(page).toHaveURL(/\/login/);
});
