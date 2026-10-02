import { test } from "@playwright/test";
import {
  createFixtureViaApi,
  getAdminUserFixture,
  Scenario,
  setupFixture,
} from "./fixtures/fixtures";
import AdminLoginPage from "./pages/AdminLoginPage";
import AdminClientDetailsPage from "./pages/AdminClientDetailsPage";

test("reports are organised according to their status", async ({ page }) => {
  const runTest = async (scenario: Scenario) => {
    const clientId = scenario["orders"][0]["clientId"];

    const adminLoginPage = new AdminLoginPage(page);
    await adminLoginPage.loginAdmin(getAdminUserFixture());

    const adminClientDetailsPage = new AdminClientDetailsPage(page, clientId);
    await adminClientDetailsPage.goto();

    await adminClientDetailsPage.expectReportsTable("active", "2026-2027");
    await adminClientDetailsPage.expectReportsTable("submitted", "2024-2025");
    await adminClientDetailsPage.expectReportsTable("incomplete", "2025-2026");

    return Promise.resolve();
  };

  await setupFixture(
    createFixtureViaApi("/fixtures/scenarios/generic", {
      reportType: "OPG102",
      reports: [
        // submitted and complete
        {
          startDate: new Date("2024-01-02"),
          submitDate: new Date("2025-01-01"),
        },

        // submitted but incomplete
        {
          startDate: new Date("2025-01-02"),
          submitDate: new Date("2026-01-01"),
          unSubmitDate: new Date("2026-01-03"),
        },

        // active (note this has to be last in this list otherwise
        // it does not become the current report, due to how the
        // FixtureService processes the ReportList)
        { startDate: new Date("2026-01-02") },
      ],
      deputies: [{ ref: "client-details-user-1", type: "LAY" }],
    }),
  ).then((fixture) => runTest(fixture.data as Scenario));
});

test("active reports on closed orders are displayed separately", async ({
  page,
}) => {
  const runTest = async (scenario: Scenario) => {
    const clientId = scenario["orders"][0]["clientId"];

    const adminLoginPage = new AdminLoginPage(page);
    await adminLoginPage.loginAdmin(getAdminUserFixture());

    const adminClientDetailsPage = new AdminClientDetailsPage(page, clientId);
    await adminClientDetailsPage.goto();

    // note that because this report is on an inactive order, the period
    // is only displayed once, as there's no "Manage" link
    await adminClientDetailsPage.expectReportsTable(
      "unsubmitted (closed court orders)",
      "2026-2027",
      1,
    );

    return Promise.resolve();
  };

  await setupFixture(
    createFixtureViaApi("/fixtures/scenarios/generic", {
      active: false,
      reportType: "OPG104",
      reports: [
        // active (note this has to be last in this list otherwise
        // it does not become the current report, due to how the
        // FixtureService processes the ReportList)
        { startDate: new Date("2026-01-02") },
      ],
      deputies: [{ ref: "client-details-user-2", type: "LAY" }],
    }),
  ).then((fixture) => runTest(fixture.data as Scenario));
});
