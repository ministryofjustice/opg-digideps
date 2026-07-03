import { test } from "@playwright/test";
import { createSimpleLay, setupScenario } from "./fixtures/fixtures";

test("a user attempts to send further documents", async ({ page }) => {
    await setupScenario(createSimpleLay)
      .then(scenario => console.log(scenario))

    // TODO test
})
