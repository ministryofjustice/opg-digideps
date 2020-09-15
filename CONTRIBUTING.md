# Contributing

We welcome contributions! Please read this file to get a feel for what the expectations are.

- [Code of conduct](#code-of-conduct)
- [Coding Conventions](#coding-conventions)
- [Opening pull requests](#opening-pull-requests)
- [Commit messages](#commit-messages)

## Code of Conduct

Civil servants on this product all follow the [Civil Service Code](https://www.gov.uk/government/publications/civil-service-code/the-civil-service-code). External contributors should review the [Code of Conduct](CODE_OF_CONDUCT.md).

## Coding Conventions

For PHP code we use PHPStan and code to [PSR-12](https://www.php-fig.org/psr/psr-12/).

For JavaScript we use ESlint.

Accessibility in frontend code is checked by Pa11y to lint for accessibility issues.

For Terraform code, TFLint is used.

Code standards are enforced by the pre-commit hooks and the build pipeline. We recommend you install [pre-commit](https://pre-commit.com/) for local devlopment.

## Opening pull requests

We have a pull request template, which will help you explain your work. It covers the purpose, approach and a checklist of key things to be sure of.

A green build on circle CI is required before a merge, along with approval from a member of the team.

We use a rebase workflow. Our primary branch is *main*. Please rebase branches on main if you need to pull in changes and use squash an merge for the final commit so we can back out changes easily.

## Commit messages

Explain what your work changes in the commit message and why it does so.
