# 6. CI Pipeline Usage

Date: 2023-02-02

## Status

Accepted

## Context


We are currently using github actions for our CI pipeline. We prefer this over circleci as it is
easier to control the permissions that each workflow and sub workflow has and the secrets that are available to it.

It also integrates more natively with github which we use as our code repository.

We have two choices with the style and content of the actions.

- Use reusable workflows
- Use composite actions

Composite actions main advantage is layout. It means we get to use a folder structure like

```
.github
    - actions
        - my-action-name
            - action.yml
    - workflows
        - workflow.yml
```

All the functionality like `if` statements and masking secrets is there (though you can't inherit all secrets,
which is probably actually a good thing).
The other advantage is that you can do 10 levels of nesting.

However the main drawback is the logging becomes quite hard to follow and for our purposes the split out logging
was so much better so we have gone with re-useable workflows. We have used _action name to denote all actions that are sub workflows.
This isn't an official convention but it does make it more obvious what is a main workflow and what is a re-usuable sub workflow.

If we do decide to go with composite actions in the future then the layout would look like this:

in workflow.yml:

```
  slack_notify_success:
    runs-on: ubuntu-latest
    needs:
      - workflow_variables
    steps:
      - uses: actions/checkout@2541b1294d2704b0964813337f33b291d3f8596b # pin@v3
      - id: slack-notify
        uses: ./.github/actions/slack-notification
        with:
          template: successful_environment_release.txt
          workflow_status: ${{ github.action_status }}
          branch: ${{ needs.workflow_variables.outputs.parsed_branch }}
          webhook: ${{ secrets.WEBHOOK }}
```

in actions folder:
```
    name: "Notify Slack"
    description: "Slack notification action"
    inputs:
      template:
        description: "Template to use"
        required: true
      workflow_status:
        description: "Status of the workflow"
        required: false
        default: failure
      branch:
        description: "Branch of the workflow"
        required: false
        default: none
      webhook:
        required: true
        description: "Webhook for the branch"

    runs:
      using: "composite"
      steps:
        - uses: actions/checkout@2541b1294d2704b0964813337f33b291d3f8596b # pin@v3

        ... (other steps go here)
```

## Decision

- Implementing github actions pipeline with reusable workflow syntax

## Consequences

- Better logging
- All the workflows and sub workflows need to be in same folder
