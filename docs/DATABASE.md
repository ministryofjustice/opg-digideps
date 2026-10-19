## Database

We currently use serverless v2 postgresql in all of our environments.

The pre-production and production databases are set up with deletion protection and also a
lifecycle variable that stops deletion of the resource. If for any reason they are
manually deleted then a final snapshot is taken.

### Upgrading RDS - Minor

We have a github action scheduled task that checks for minor updates and upgrades all envs except prod on the first week.
Then on the second week an automated PR is raised to upgrade prod to the same version.

### Upgrading RDS - Major

To perform an upgrade of a major version, it is best to push through a PR with the new version in and
either leave out production and apply in a separate PR or make sure to wait
until the maintenance window has completed on preproduction and that you have done
a manual check of the environment before pushing out to production.
