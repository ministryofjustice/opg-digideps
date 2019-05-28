# Rollback Procedure

In the event of a broken application that is not easily rectified and can be attributed to a particular release, we can rollback the application code, infrastructure code, and if necessary, the database schema, to a previous release tag.

If a database schema rollback is required, the following prerequisites apply:
* Requires a developer or devops engineer with Cloud 9 access to the production database.
* Cloud 9 environment is provisioned sufficiently to enable doctrine migrations to be executed (see [AWS-DB-ACCESS.md](https://gitlab.service.opg.digital/opsforks/opg-digi-deps-deploy/blob/master/terraform/AWS-DB-ACCESS.md))

##### Step 1: Invoke the [Provision_Environment](https://jenkins.service.opg.digital/job/Digi-Deps/view/Master%20Pipeline/job/Provision%20Environment/) task in Jenkins:
* ENVIRONMENT: master
* DEPLOY_VERSION: [tag to rollback to]
* RELEASE_TAG: [tag to rollback to]

##### Step 2: Invoke the [Test_Master](https://jenkins.service.opg.digital/job/Digi-Deps/view/Master%20Pipeline/job/Test_Master/) task in Jenkins:

From this point onwards, the Jenkins build triggers will push the build through the Master pipeline, subsequently deploying to preprod and training.

##### Step 3: Verify build on preprod.

##### Step 4: Release from preprod to production as per our current release process.

#### Database schema rollback (if applicable):
##### Step 1: Access your provisioned Cloud 9 environment which contains a copy of the `opg-digi-deps-api` repo that is configured to access the production database.

##### Step 2: From the command prompt of the Cloud 9 server `cd` into the `opg-digi-deps-api` directory and run `git pull origin master`.

##### Step 3: Using the integrated IDE, comment out any “CREATE SCHEMA public” commands in the `down()` method of any migration files that will be rolled back
An unresolved bug in Doctrine inadvertently adds this command in auto generated migrations.

##### Step 4: Run the PHP migration command from the command prompt, using the latest migration that you want to migrate to.
For example, if migrations 103 and 102 need rolling back, we want run our migrations back to 101:

`php app/console doctrine:migrations:migrate 101 -n` 

##### Step 5: Verify status of database and application.




