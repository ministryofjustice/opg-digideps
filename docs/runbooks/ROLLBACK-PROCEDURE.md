# Rollback Procedure

In the event of a broken application that is not easily rectified and can be attributed to a particular release, we can rollback the application code, infrastructure code, and if necessary, the database schema, to a previous release tag.

If a database schema rollback is required, the following prerequisites apply:
* Requires a developer or devops engineer with Cloud 9 access to the production database.
* Cloud 9 environment is provisioned sufficiently to enable doctrine migrations to be executed (see [AWS-DB-ACCESS.md](https://gitlab.service.opg.digital/opsforks/opg-digi-deps-deploy/blob/master/terraform/AWS-DB-ACCESS.md))

CircleCI builds off commits and pull requests and our environment needs to reflect what is in main branch in github.

As such if a PR has somehow got through all the tests and environments but still managed to cause as issue and needs to be pulled then you should do the following.

##### Step 1: Find out if it's causing an outage or a serious enough issue that it needs to be rectified immediately
If it is then you should talk to a developer or web ops on the team to perform following steps:
- Get the previous successful tag of the application that was released to production. In CircleCI you can find this on the build step of the last successful `main` branch under `build integration-1` -> `show version`. This information is also under jira releases if you have access to this.
- Go to `enviroment` folder and run `export TF_VAR_DEFAULT_ROLE=breakglass` and `export TF_WORKSPACE=production02`
- Run `terraform plan` and enter the tag when requested. Check it's doing what you expect.
- Run `terraform apply` and enter the tag when requested.
This will deploy the previous version of the application whilst you revert the changes in github that caused the issues. If infrastructure issues have caused the problem then you will need to revert them as below and apply from your console.

##### Step 2: Checkout a new branch and revert the commit

All of our commits are squashed merged so it will be one commit to revert for the problematic issue.
- Checkout main branch and check it's up to date `git checkout main && git pull origin main`
- Checkout your ticket and append revert: `git checkout DDPB-0000revert`
- Find the commit with `git log`
- Revert the commit with `git revert <commit hash>`
- Push to your new branch and create a PR

This should revert all changes you made that caused the issue including if applicable infrastructure changes.

##### Step 3: Verify it passes all necessary tests and merge PR

##### Step 4: Release through all environments to production as per our current release process.

#### Database schema rollback (if applicable):

##### Step 1: Access your provisioned Cloud 9 environment in PreProduction.

##### Step 2: Clone digideps repo.

##### Step 3: Run the PHP migration command from the command prompt, using the latest migration that you want to migrate to.
For example, if migrations 103 and 102 need rolling back, we want run our migrations back to 101:
`php app/console doctrine:migrations:migrate 101 -n`

##### Step 4: Verify status of database and application.

##### Step 5: Run a manual snapshot in production and repeat process in production
