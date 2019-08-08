# Contributing to Digi Deps

Most communication is done through Ministry of Justice Digital & Technology Slack in the `opg-digideps-team` and `opg-digideps-devs` channels.

## Pre-requisites

- Access to the [Ministry of Justice Digital & Technology Slack](https://mojdt.slack.com/)
- Access to [JIRA](https://opgtransform.atlassian.net/secure/RapidBoard.jspa?projectKey=DDPB)

## Stand-ups
Stand-ups are daily at 0945 via Google Meet. Link is available in the team Slack channel. Stand-up meetings should be concluded by 1000 at latest.

If unavailable for a stand-up, updates can be posted in the team Slack channel.

## Development lifecycle

Development is organised into two week sprints, starting and ending on a Tuesday.

### Planning

Planning takes place on [JIRA](https://opgtransform.atlassian.net/secure/RapidBoard.jspa?projectKey=DDPB), with sprints scheduled at planning meetings on Tuesday. Additional issues can be added throughout the sprint if there is insufficient work for developers.

### Development

When ready to work, identify an unassigned issue in the "To Do" column, move it to "In Progress" and assign yourself.

You should generally only have one issue assigned to yourself in the "In Progress" column.

Stories should be estimated and have acceptance criteria before development begins (other types of issues, such as bugs, don't require these details).

You should write your code on a branch named after the JIRA issue and a description. For example: `DDPB-2851-add-prof-deputy-money-out`

During development you should add tests to check any new functionality works, and ensure that existing tests are not broken.

### Peer review

Once you're happy with your work, push the branch to GitHub and create a pull request. Add a comment to the issue explaining your changes and including a link to the pull request.

You should then move it to the "Review" column to indicate that it is ready for peer review, and post a link to the issue in the dev Slack channel to alert a reviewer. If there is someone particular you would like to review the pull request, you can request a review from them through GitHub.

### OPG review

Once your work has been suitably reviewed and you've ensured that all tests are passing, it can be tested by the product team.

For your changes to be tested, you need to deploy your branch to a feature environment. You can identify which feature environments are available by looking at prefixes of tickets in JIRA. Prefix your ticket with the environment you're using (e.g. `[F1]`)

See the [deployment documentation][deployment-docs] for details of how to deploy to a feature environment.

You should then move your JIRA issue into the "OPG Acceptance" column and assign a product manager to test it. If the issue was originally reported by a product manager, you should assign it back to them to test. Otherwise, you may assign it to any product manager and they will pass it on as necessary.

### Approval

If the product manager approves your work, they will move the JIRA issue to the "Ready for Release" column. At this point you should complete your pull request and merge the changes into `master`.

After the pull request has completed, the changes are automatically tested and, if the tests pass, deployed to pre-production and training environments.

### Releasing

On a regular basis, the development team will review issues in the "Ready for Release" column and promote them to the production environment. This work is co-ordinated over Slack.

See the [deployment documentation][deployment-docs] for details of how to deploy to production.

[deployment-docs]: docs/DEPLOYMENT.md
