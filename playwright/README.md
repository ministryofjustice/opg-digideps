# Playwright End-to-End Tests

This project contains end-to-end tests for Digideps using Playwright.
All commands are designed to run inside Docker to ensure a consistent and reproducible environment.

---

## Available Commands

All commands are exposed via the `Makefile` and wrap Docker Compose.

### Run tests
```bash
make playwright-test
```

Runs the full Playwright test suite in a container.

---

### View test report
```bash
make playwright-report
```

Serves the HTML report from the last test run.

Once running, open:
```
http://localhost:9323
```

---

### Run linter
```bash
make playwright-lint
```

Runs ESLint against all test files.

---

### Run Playwright UI (interactive mode)
```bash
make playwright-ui
```

Starts the Playwright UI for interactive debugging.

Once running, open:
```
http://localhost:9525
```

Important - the link it gives you to 0.0.0.0 won't work. It only resolves on localhost!

This allows you to:
- Run tests individually
- Debug test steps
- Inspect browser behaviour

---

### Check formatting
```bash
make playwright-check-format
```

Checks code formatting using Prettier.

---

### Format code
```bash
make playwright-format
```

Automatically formats the test code using Prettier.

---

## CI Expectations

The CI pipeline enforces:

- ESLint (`playwright-lint`)
- Prettier formatting (`playwright-check-format`)

### Before opening or merging a PR

You should run:

```bash
make playwright-lint
make playwright-check-format
```

This ensures:
- Your code meets linting rules
- Your formatting matches project standards
- CI does not fail unnecessarily

---

## Notes

- All commands run inside Docker — no local Node or Playwright setup is required.
- Ports used:
  - `9323` → HTML report
  - `9525` → Playwright UI

---

## Tip

For local debugging, `playwright-ui` is the fastest way to iterate on tests.
For CI parity, use `playwright-test`.
