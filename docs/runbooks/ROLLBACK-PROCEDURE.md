# Rollback Procedure

In the event of a broken application that is not easily rectified and can be attributed to a particular release, we can rollback the application code, infrastructure code, and if necessary, the database schema, to a previous release tag.

As such if a PR has somehow got through all the tests and environments but still managed to cause as issue and needs to be pulled then you should do the following.

##### Step 1: Decide if you need to start an incident

If the release is affecting production in a significant way then you should create an incident in slack and invite interested parties
by using the /opg-incident slack command.

##### Step 2: Find out if it's causing an outage or a serious enough issue that it needs to be rectified immediately

This means that it is enough of an issue that the time to revert is too great for a simple revert of your PR to go out either
because it has taken the service down or it will cause data loss or severe reputation damage.

Skip to the next step if the impact is minimal as this step does not follow our usual release procedure.

Assuming you decide to go ahead then you should talk to a web-ops team member to perform following steps:
- Get the previous successful tag of the application that was released to production. In Github actions you can find this on the build step of the last successful `main` branch that went live. Open any of the build jobs in that workflow and look in `show build tag`.
- Go to `terraform/enviroment` folder and run `export TF_VAR_DEFAULT_ROLE=breakglass` and `export TF_WORKSPACE=production02`
- Run `terraform plan` and enter the tag you copied previously. Check it's doing what you expect.
- Run `terraform apply` and enter the tag you copied previously when requested.

This will deploy the previous version of the application whilst you revert the changes in github that caused the issues. This will only fix issues that are caused by application code changes.

If infrastructure issues have caused the problem then you will need to revert them first as below (or just checkout main) and apply from your console using terraform.

#### Step 3: Checkout a new branch and revert the commit

All of our commits are squashed at merge so it will be one commit to revert for the problematic issue.
- Checkout main branch and check it's up to date `git checkout main && git pull origin main`
- Checkout your ticket and append revert: `git checkout -b DDLS-XXXXrevert`
- Find the commit with `git log`
- Revert the commit with `git revert <commit hash>`
- Push to your new branch and create a PR

This should revert all changes you made that caused the issue including, if applicable, infrastructure changes.

#### Step 4: Verify it passes all necessary tests and merge PR

#### Step 5: Release through all environments to production as per our current release process.

## Database schema rollback (if applicable):

If a database schema rollback is required, the following prerequisites apply:
* Requires a developer or devops engineer with Cloud 9 access to the production database.
* Cloud 9 environment is provisioned sufficiently to enable doctrine migrations to be executed (see [AWS-DB-ACCESS.md](https://gitlab.service.opg.digital/opsforks/opg-digi-deps-deploy/blob/master/terraform/AWS-DB-ACCESS.md))

- Step 1: Access your provisioned Cloud 9 environment in PreProduction.

- Step 2: Clone digideps repo.

- Step 3: Run the PHP migration command from the command prompt, using the latest migration that you want to migrate to.
For example, if migrations 103 and 102 need rolling back, we want run our migrations back to 101:
`php app/console doctrine:migrations:migrate 101 -n`

- Step 4: Verify status of database and application.

- Step 5: Run a manual snapshot in production and repeat process in production

## Full Database Restore to Point in Time

In the worst case scenario, we may have released something that deletes or updates our data in an unexpected way
and we need to revert our DB to a previous point in time. Obviously this should only be performed as a last resort
but instructions can be found here: [Disaster Recovery](../../docs/DISASTER_RECOVERY.md)

In this highly unlikely circumstance, you would make sure that the code change that caused the issue was fully rolled
back in production before performing the restore.
