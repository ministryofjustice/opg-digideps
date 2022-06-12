## Database

We currently use serverless postgresql in our dev environments and provisioned RDS
postgresql clusters in our pre-production and production environments.

The pre-production and production databases are set up with deletion protection and also a
lifecycle variable that stops deletion of the resource. If for any reason they are
manually deleted then a final snapshot is taken.

To perform an upgrade it is best to push through a PR with the new version in and
either leave out production and apply in a separate PR or make sure to wait
until the maintenance window has completed on preproduction and that you have done
a manual check of the environment before pushing out to production.
