# Create new environment

There are many reasons you may wish to create a new environment. All of our PRs create branch based environments
that are removed on merge of the code. For normal purposes this should be how you test and deploy your code.

However there may be a need for an additional permanent environment due to for example:

- A new requirement for users
- A change of account of existing environment
- A broken and unrecoverable environment

In all cases the basic principal is the same.

- Add the new environment to the `.circleci/config.yml` by adding this code block:
```
      - terraform-command:
          name: apply <add your new environment name here>
          requires: [ <add the job you want it to follow here> ]
          filters: { branches: { only: [ main ] } }
          tf_workspace: <add your new environment name here>
          tf_command: apply
```

- Add additional steps like a plan / reset database / restore into etc as applicable. Follow other environment
examples in the circle config for the exact syntax.

- Create a PR and merge in your new code and allow it to build the new environment.

- If you need to restore a real database in as a one time only thing (such as recreating production) then once your
new environment is created follow the restore DB instructions [disaster recovery document](https://github.com/ministryofjustice/opg-digideps/blob/main/docs/DISASTER_RECOVERY.md).

- Finally if you're creating a new version of production then once you have checked the environment is working as expected,
you have to switch the DNS over. So switch current prod to true and new to false for the below (part of tfvars) and create a PR.
```
"subdomain_enabled": false,
```
