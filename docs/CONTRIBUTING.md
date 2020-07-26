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

You should write your code on a branch named after the JIRA issue. For example: `DDPB-2851`.

During development you should add tests to check any new functionality works, and ensure that existing tests are not broken.

Push the branch to GitHub and create a pull request. Fill out the PR template to explain your changes. CircleCI will automatically create a new feature environment in AWS named after your branch.

### Peer review

Once you're happy with your work, you should move it to the "Code Review" column to indicate that it is ready for peer review, and post a link to the issue in the dev Slack channel to alert a reviewer.

### Acceptance

Once your work has been suitably reviewed and you've ensured that all tests are passing, it can be approved by the product team.

JIRA issues should be moved to the "Acceptance" column and have a product manager assigned. PMs will then do any necessary manual checking and approve the work for release.

### Ready to merge

If the product manager approves your work, they will move the JIRA issue to the "Ready for Release" column. At this point you should approve the destruction of the feature environment in CircleCI, and merge the branch into `main`.

After the pull request has completed, the changes are automatically tested and, if the tests pass, deployed to pre-production.

### Releasing

On a regular basis, the development team will review issues in the "Ready for Release" column and promote them to the production environment. This work is co-ordinated over Slack.

See the [release procedure][release-procedure] for details of how to deploy to production.

## Verification

The "Verification" column on JIRA should be used for tickets which need post-release verification. For example this may include checking a user's problem is resolved, monitoring service health, or waiting for activation from a third party.

[release-procedure]: https://opgtransform.atlassian.net/wiki/spaces/DigiDeps/pages/59736181/Release+procedure
