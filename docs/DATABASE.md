## Database

We currently use aurora in our dev environments and RDS postgres 9.6.x in our
pre-production and production environments.

The pre-production and production databases are set up with deletion protection and also a lifecycle variable that stops
deletion of the resource. If for any reason they are manually deleted then a final snapshot is taken.

To control our upgrade of minor versions we have put in place a staged deployment.

Out of the non Dev environments only main is set to auto increment the minor version. This happens during the maintenance window.

Pre-production is setup to pull the current version from main which will have been through the full test suites and will set itself to
update during it's maintenance window. Production then pulls from pre-production. This is done via the output in the previous jobs state file.

So an upgrade will look something like this:

#### Maintenance window 1
- Main upgrades from 9.6.12 to 9.6.13
- The same night as maintenance window, workflow if automatically kicked off to run all the tests and we are alerted of any issues
- On next apply of pre-production it will pull the new 9.6.13 version from main and apply it to itself. This doesn't update straight away
but puts in an event in the pre-production maintenance window to upgrade at next maintenance.
- On apply to Production there is no update as prod still sees Pre-Production set to 9.6.12 the same as what it is set to.

#### Maintenance window 2

- Main is still at 9.6.13
- Pre-production has now upgraded to 9.6.13, no changes to apply as same as main
- Production sees that Pre-production is ahead and adds event to it's maintenance window to upgrade to 9.6.13

#### Maintenance window 3

- Main is still at 9.6.13
- Pre-production at 9.6.13
- Production has now upgraded to 9.6.13

#### ==== Information ====

It's worth bearing in mind that the terraform will try and update the RDS version until the maintenance window is applied.
Whilst this looks concerning in the terraform plan, it doesn't affect database connections. Once it is aligned with a version of
previous env it will stop doing this.
