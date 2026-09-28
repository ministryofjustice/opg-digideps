import DeputySession from "./DeputySession";
import type { Page } from "@playwright/test";
import LoginPage from "../pages/LoginPage";
import AdminLoginPage from "../pages/AdminLoginPage";
import AdminSession from "./AdminSession";

export async function loginAsDeputy(
  page: Page,
  email: string,
  password: string,
): Promise<DeputySession> {
  const context = await page.context().browser()?.newContext();
  if (context === undefined) {
    throw new Error("Not in a browser context!");
  }
  const session = new DeputySession(await context.newPage());
  const loginPage = new LoginPage(session.page);
  await loginPage.goto();
  await loginPage.login({ email, password });
  return session;
}

export async function loginAsAdmin(
  page: Page,
  email: string,
  password: string,
): Promise<DeputySession> {
  const context = await page.context().browser()?.newContext();
  if (context === undefined) {
    throw new Error("Not in a browser context!");
  }
  const session = new AdminSession(await context.newPage());
  const loginPage = new AdminLoginPage(session.page);
  await loginPage.loginAdmin({ email, password });
  return session;
}
