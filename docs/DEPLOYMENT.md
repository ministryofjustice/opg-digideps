# Deployment

There are three stages of deployment:

- To a feature environment for acceptance testing
- To preproduction for integration testing
- To production for a release

Deployment is managed through [Jenkins][jenkins].

## Feature environment deployment

When your PR is ready for acceptance testing, you can use Jenkins to manually deploy it to a feature environment.

You first need to build docker images with the **Build_Feature_Branch** job. You will be asked to specify which API, client and docker branches to build.

Your images will be assigned a tag. You can identify this either through the GitHub [releases page][client-releases] or by looking at the associated **build_docker_images** job in Jenkins.

You can then use the **Deploy Tag to Feature Environment** to deploy your tag to an available feature environment.

You should also run the **Test_Feature** job after deployment to ensure automated tests all pass.

## Preproduction deployment

Deployment to master, training and preproduction environments happens automatically when your pull request is merged.

The automated tests are run after deployment to master and must pass before deploying to preproduction.

## Production deployment

To prepare a release, you should create a [version in JIRA][jira-versions] and assign the relevant tickets to it. All tickets in the version must have been merged into master and the automated tests must have passed.

You can identify the version number from the footer of the master and preproduction environments (which should be identical).

Once the release has been signed off by product managers and preproduction smoke tests have been performed, you can use the **Deploy Tag to Production from Preproduction** to deploy to production.

After the deployment has completed, confirm the version number in the footer of the [live service][service] and check the [availability report][service-availability]. A developer should perform the production smoke tests.

You should now mark the JIRA version as released, the tickets as done, and notify the team that the release has been completed.

[jenkins]: https://jenkins.service.opg.digital/job/Digi-Deps/
[jenkins-release]: https://jenkins.service.opg.digital/job/Digi-Deps/view/Release/
[jira-versions]: https://opgtransform.atlassian.net/projects/DDPB?selectedItem=com.atlassian.jira.jira-projects-plugin%3Arelease-page
[client-releases]: https://github.com/ministryofjustice/opg-digi-deps-client/releases
[service]: https://www.complete-deputy-report.service.gov.uk/
[service-availability]: https://www.complete-deputy-report.service.gov.uk/manage/availability
