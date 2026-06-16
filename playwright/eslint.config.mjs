import { defineConfig, globalIgnores } from "eslint/config";
import tseslint from "typescript-eslint";
import globals from "globals";

export default defineConfig([
  globalIgnores(["**/playwright-report/"]),
  {
    files: ["**/*.ts", "**/*.cts", "**/*.mts"],
    languageOptions: { globals: globals.browser },
  },
  tseslint.configs.strict,
]);
