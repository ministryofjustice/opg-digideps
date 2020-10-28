# Release Procedure

Releases are managed by the DigiDeps team

Ensure tickets in the Release column are merged into master have deployed built to preproduction

* Check preproduction:
1) [Deputy](https://preproduction.complete-deputy-report.service.gov.uk/) and [admin](https://admin.preproduction.complete-deputy-report.service.gov.uk/) can log in
2) Availability pages don't show errors

* Create a new release in Jira
1) Confirm release version number is the same as preprod (check the meta file or the page footer)
2) Tag tickets in the Release column with this "Fix version"

* Approve the release in the relevant (usually most recent) workflow on CircleCI (master branch)
1) If there are any older master builds, cancel them
2) Migrations will automatically be applied by one of the API tasks on startup

* Check production website:
1) Version number matches release
2) [Deputy](https://complete-deputy-report.service.gov.uk/) and [admin](https://admin.complete-deputy-report.service.gov.uk/) can log in

* Availability pages don't show errors

* Notify team that release has been completed

* Move tickets to Done (or Verification, if necessary)

* Mark Jira release as released in the releases tab
