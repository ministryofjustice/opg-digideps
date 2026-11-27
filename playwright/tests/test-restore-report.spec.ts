import { test } from "@playwright/test";
import {
  createFixtureViaApi,
  getAdminUserFixture,
  Scenario,
  setupFixture,
} from "./fixtures/fixtures";
import AdminLoginPage from "./pages/AdminLoginPage";
import AdminClientDetailsPage from "./pages/AdminClientDetailsPage";
import AdminRestoreReportPage from "./pages/AdminRestoreReportPage";
import PageOpgBanner from "./pageComponents/PageOpgBanner";

test("a PFA report on a closed court order can be restored over the active PFA report", async ({
  page,
}) => {
  const runTest = async (scenarios: Scenario[]) => {
    const clientId = scenarios[0]["orders"][0]["clientId"];
    const replaceableReportId = scenarios[1]["orders"][0]["reports"][0]["id"];
    const restoreFromReportId = scenarios[0]["orders"][0]["reports"][0]["id"];

    const adminLoginPage = new AdminLoginPage(page);
    await adminLoginPage.loginAdmin(getAdminUserFixture());

    const adminClientDetailsPage = new AdminClientDetailsPage(page, clientId);
    await adminClientDetailsPage.goto();
    const clientName = await page
      .getByRole("heading", { level: 1 })
      .filter({ hasText: scenarios[0].client.lastName })
      .innerText();

    await adminClientDetailsPage.expectReport("active", "2025-2026", "OPG103");
    await adminClientDetailsPage.expectReport(
      "unsubmitted (closed court orders)",
      "2024-2025",
      "OPG102",
    );

    // confirm that the report on the active court order is swappable with
    // the report on the closed court order
    await adminClientDetailsPage.restoreReport("2024-2025");

    const adminRestoreReportPage = new AdminRestoreReportPage(
      page,
      replaceableReportId,
      restoreFromReportId,
      clientId,
    );
    await adminRestoreReportPage.isExpected();
    await adminRestoreReportPage.expectConfirmation({
      courtOrderReferences: scenarios[0].orders
        .map((order) => order.courtOrderUid)
        .join(", "),
      clientName,
      reportType: "Personal and Financial Affairs",
      period: "1 July 2024 to 30 June 2025",
    });
    await adminRestoreReportPage.confirmRestore();

    await new PageOpgBanner(page).expectSuccessMessage(
      "Report restore complete",
    );
    // the reports have swapped court orders, and each keeps its own dates, so
    // the active report now has the dates of the report restored from; the
    // restored report also takes the type of the report it replaced
    await adminClientDetailsPage.expectReport("active", "2024-2025", "OPG103");
    await adminClientDetailsPage.expectReport(
      "unsubmitted (closed court orders)",
      "2025-2026",
      "OPG103",
    );
  };

  await setupFixture(
    createFixtureViaApi("/fixtures/scenarios/generic", [
      // unsubmitted report on closed court order (can replace active report)
      {
        reportType: "OPG102",
        active: false,
        reports: [
          {
            startDate: new Date("2024-07-01"),
          },
        ],
      },
      // unsubmitted report on active court order (can be replaced by report
      // on closed order)
      {
        reportType: "OPG103",
        active: true,
        reports: [
          {
            startDate: new Date("2025-07-01"),
          },
        ],
      },
    ]),
  ).then((fixture) => runTest(fixture.data as Scenario[]));
});
