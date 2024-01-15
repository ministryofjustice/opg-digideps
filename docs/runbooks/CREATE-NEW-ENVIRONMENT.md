# Create New Environment

There are many reasons you may wish to create a new environment. All of our PRs create branch based environments
that are removed on merge of the code.

Sometimes though, there may be a need for an additional permanent environment due to for example:

- A new requirement for users
- A change of account of existing environment
- A broken and unrecoverable environment
- An environment in a new region due to region wide outage or legislative change

In all cases the basic principal is the same.

- Add the new environment to the `.github/workflow-path-to-live.yml` by adding a new block for:
```
  terraform_apply_integration:
    name: integration environment apply terraform
    uses: ./.github/workflows/_run-terraform.yml
    needs:
      - terraform_apply_account_preproduction
      - workflow_variables
    with:
      workspace: my_new_environment_name
      terraform_path: environment
      apply: true
      container_version: ${{ needs.workflow_variables.outputs.build_identifier }}-${{ needs.workflow_variables.outputs.short_sha }}
    secrets: inherit
```
Check that the needs block is right and the needs block of subsequent jobs and choose your environment name
instead of my_new_environment_name.

- Add additional steps like a plan / reset database / restore as applicable. Follow other environment
examples in the github actions ymls for the exact syntax.

- Update terraform.tfvars.json with the new environment details.

- Create a PR and merge in your new code and allow it to build the new environment.

- If you need to restore a real database in as a one time only process (such as recreating production) then once your
new environment is created follow the restore DB instructions [disaster recovery document](https://github.com/ministryofjustice/opg-digideps/blob/main/docs/DISASTER_RECOVERY.md).

- Finally if you're creating a new version of production then once you have checked the environment is working as expected,
you have to switch the DNS over. So switch current production `subdomain_enabled` flag to true and the new environments
to false in the tfvars file and create a PR.
```
"subdomain_enabled": false,
```
