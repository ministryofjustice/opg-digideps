import DeputySession from "./DeputySession";
import type { Browser } from "@playwright/test";
import LoginPage from "../pages/LoginPage";
import AdminLoginPage from "../pages/AdminLoginPage";
import AdminSession from "./AdminSession";

export async function loginAsDeputy(
  browser: Browser,
  email: string,
  password: string,
): Promise<DeputySession> {
  const session = new DeputySession(
    await (await browser.newContext()).newPage(),
  );
  const loginPage = new LoginPage(session.page);
  await loginPage.goto();
  await loginPage.login({ email, password });
  return session;
}

export async function loginAsAdmin(
  browser: Browser,
  email: string,
  password: string,
): Promise<DeputySession> {
  const session = new AdminSession(
    await (await browser.newContext()).newPage(),
  );
  const loginPage = new AdminLoginPage(session.page);
  await loginPage.loginAdmin({ email, password });
  return session;
}
