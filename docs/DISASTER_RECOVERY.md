## Digideps - Disaster Recovery Plan

#### Resilience & Data Recovery

- 14 day db Snapshot stored on AWS. AWS DB snapshots redundantly stores data in multiple facilities and on multiple devices within each facility. To increase durability, Amazon synchronously stores your data across multiple facilities before confirming that the data has been successfully stored.

- Daily backup of snapshots to separate account in the unlikely event the account were to be compromised. We keep the last 7 days worth of backups there.

- Process to be tested every 6 months - 1 year (or when team make significant changes)

- Database split over availability zones so that it will failover to another zone if current zone goes down

- Redis split over availability zones so that it will failover to another zone if current zone goes down.

- Based on full take up of our digital service, we don’t expect more than 70 users at any given time to be on service in the near future unless there is some significant change in policy or in functionality to the service.

#### Business appetite for time to recovery
- **Caseworker** side - 48 hours

- **Deputy side** - 5 working days
All of our measures to restore the service fall within the 48 hours specified by the business.

#### Instructions to restore from snapshot

1) Sign in to the AWS Management Console and open the Amazon RDS console at https://console.aws.amazon.com/rds/.
2) In the navigation pane, choose Snapshots.
3) Choose the DB cluster snapshot that you want to restore from.
4) For Actions, choose Restore Snapshot.
5) On the Restore DB Instance page, for DB Instance Identifier, enter the name for your restored DB cluster.
6) Choose Restore DB Instance.

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
