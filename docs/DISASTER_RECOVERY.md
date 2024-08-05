## Digideps - Disaster Recovery Plan

#### Resilience & Data Recovery

- 14 day db Snapshot stored on AWS. AWS DB snapshots redundantly stores data in multiple facilities and on multiple devices within each facility. To increase durability, Amazon synchronously stores your data across multiple facilities before confirming that the data has been successfully stored.

- Daily backup of snapshots to separate account in the unlikely event the account were to be compromised. We keep the last 7 days worth of backups there.

- Process to be tested every 6 months - 1 year (or when team make significant changes)

- Process runs weekly against one of our test environments to make sure it continues to work

- Database split over availability zones so that it will failover to another zone if current zone goes down

- Redis split over availability zones so that it will failover to another zone if current zone goes down.

- Based on full take up of our digital service, we don’t expect more than 70 users at any given time to be on service in the near future unless there is some significant change in policy or in functionality to the service.

#### Business appetite for time to recovery
- **Caseworker** side - 48 hours

- **Deputy side** - 5 working days
All of our measures to restore the service fall within the 48 hours specified by the business.

#### Instructions to restore from snapshot

1) Sign in to the AWS Management Console and open the Amazon RDS console at https://console.aws.amazon.com/rds/.
2) Rename the current cluster appending `-bck` to the end of it
3) Rename any instances appending `-bck` to the end of them
4) In the navigation pane, choose Snapshots.
5) Choose the DB cluster snapshot that you want to restore from.
6) For Actions, choose Restore Snapshot.
7) On the Restore DB Cluster page, for DB Cluster Identifier, enter the name for your restored DB cluster.
8) Choose Restore DB Cluster.
9) Create the correct number of instances
10) Run terraform plan against the environment and check that you have restored the DB as terraform state was expecting
11) Delete the old `-bck` cluster and instances

This process can be vastly simplified if you use our automated restore container:

Run `docker compose -f docker-compose.commands.yml up dr-restore` to see a help file

You can then run various options. Some examples below:

Title: Restore from point in time to the same instance
Example disaster: An update was run directly on database that had huge unintended consequences
and you're willing to accept a small loss of data that would happen in the intervening time.
Remediation: Restore to a time before the query was run.
```
aws-vault exec identity --duration=2h -- \
docker compose -f docker-compose.commands.yml run dr-restore \
python3 database_restore.py --cluster_from api-ddpb4341 --pitr '2022-01-01 09:10:00'
```

Title: Restore from a snapshot
Example disaster: You have somehow managed to destroy all the data whilst building a database
so can no longer do a point in time recovery.
Remediation: Restore from an existing snapshot in the environment.
```
aws-vault exec identity --duration=2h -- \
docker compose -f docker-compose.commands.yml run -rm dr-restore \
python3 database_restore.py \
--cluster_from api-ddpb9999 --snapshot_id api-9999-2022-01-01-12-30
```

Title: Restore from a snapshot in backup account
Example disaster: Account was compromised and all the snapshots and DBs were deleted.
You have managed to rebuild prod using terraform and need latest data.
Remediation: Restore from an existing snapshot in backup account (use the name of snapshot stored in backup).
```
aws-vault exec identity --duration=2h -- \
docker compose -f docker-compose.commands.yml run -rm dr-restore \
python3 database_restore.py \
--cluster_from api-ddpb9999 --snapshot_id api-9999-2022-01-01-12-30 --restore_from_remote True
```

Title: Restore to a different cluster
Example disaster: You have no idea when something bad happened to the data and want to go back in time on
another instance and see if the data was ok at a point in time without affecting your main DB operation.
Remediation: Restore from an existing DB to a point in time into another cluster.
```
aws-vault exec identity --duration=2h -- \
docker compose -f docker-compose.commands.yml run -rm dr-restore \
python3 database_restore.py \
--cluster_from api-ddpb9999 --cluster_to api-new_test_cluster --pitr '2022-01-01 09:10:00'
```

| Disaster                                                                                                           | Severity | Likelihood | Recovery                                                                                                                                                                                                                       |
|--------------------------------------------------------------------------------------------------------------------|----------|------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Loss of data due to accidental/intentional misconfiguration of AWS resources                                       | High     | Low        | - Db snapshot restore                                                                                                                                                                                                          |
| Intentional disruption by malicious actor e.g. DDoS                                                                | High     | Medium     | - Auto-scaling<br>- Implementing maintenance page when threshold is met to avoid over-scaling<br>- AWS Shield is a managed Distributed Denial of Service (DDoS) protection service that safeguards applications running on AWS |
| AWS Region outage                                                                                                  | High     | Low        | - Wait<br>- Rebuild in a different region with Terraform                                                                                                                                                                       |
| Deletion of data by disgruntled former employee (Pro/PA deputy users)                                              | Medium   | Low        | - Manually fix the data from a separate restore of database<br>DB snapshot	restore		                                                                                                                                      |
| Deletion of data by WebOps/Dev unintentionally on console .i.e DB/S3 deletion, unrecoverable encryption key change | High     | Low        | - Restore from snapshot<br>- Restore from opg backup account.                                                                                                                                                                  |
| AWS account compromise and ransac i.e malicious actor                                                              | High     | Low        | - Infrastructure could easily be rebuilt<br>- Restore from opg backup account.                                                                                                                                                 |
| Dependency poisoning attacks                                                                                       | High     | Low        | - Using Dependabot<br>- Scanning on pipeline - NPM audit<br>- Container scanning<br>Dependency assessment criteria<br>Guardduty monitors VPC flow logs for unusual activity                                                    |
| Accidental misconfiguration of code leading to data loss                                                           | High     | Low        | - Manually fix in the DB where possible<br>- DB snapshot	restore
