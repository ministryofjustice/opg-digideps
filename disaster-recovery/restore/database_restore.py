import math

import boto3
import click
import os
import time
from datetime import datetime
from botocore.credentials import RefreshableCredentials
from botocore.session import get_session

environments = {
    "development": "248804316466",
    "preproduction": "454262938596",
    "backup": "238302996107",
}

key_alias_remote = "alias/digideps-ca-db-backup"
key_alias_local = "alias/aws/rds"


class SnapshotManagement:
    def __init__(
        self,
        environment: str,
        cluster_from: str,
        cluster_to: str,
        snapshot_id: str,
        multi_az_override: str,
        engine_mode_override: str,
        pitr: str,
        restore_from_remote: bool,
    ):
        self.sts_client = boto3.client("sts")
        self.account = (
            None
            if environment not in environments.keys()
            else environments[environment]
        )
        self.backup_account = environments["backup"]
        self.role = "digideps-ci" if os.getenv("CI") else "breakglass"
        self.backup_role = (
            "cross-acc-db-restore.digideps-development"
            if os.getenv("CI")
            else "breakglass"
        )
        self.role_to_assume = str(f"arn:aws:iam::{self.account}:role/{self.role}")
        self.backup_role_to_assume = str(
            f"arn:aws:iam::{self.backup_account}:role/{self.backup_role}"
        )
        self.region = "eu-west-1"
        self.client = None
        self.client_kms = None
        self.client_backup_rds = None
        self.client_backup_kms = None
        self.restore_from_remote = restore_from_remote
        self.example_var = None
        self.instances = []
        self.db_cluster_identifier_source = cluster_from
        self.db_cluster_identifier_target = (
            self.db_cluster_identifier_source if cluster_to is None else cluster_to
        )
        self.same_target = (
            True
            if self.db_cluster_identifier_target == self.db_cluster_identifier_source
            else False
        )
        self.SnapshotIdentifier = snapshot_id
        self.multi_az_override = multi_az_override
        self.MultiAZ = False
        self.engine_mode_override = engine_mode_override
        self.EngineMode = None
        self.serverless_v2_config = {}
        self.PointInTimeRecovery = datetime.strptime(pitr, "%Y-%m-%d %H:%M:%S")
        self.AllocatedStorage = 1
        self.AvailabilityZones = ["eu-west-1b", "eu-west-1a", "eu-west-1c"]
        self.BackupRetentionPeriod = 14
        self.DBClusterParameterGroup = "default.aurora-postgresql10"
        self.DBSubnetGroup = "private"
        self.Engine = "aurora-postgresql"
        self.EngineVersion = "13.12"
        self.VpcSecurityGroups = None
        self.StorageEncrypted = True
        self.KmsKeyId = None
        self.KmsKeyIdLocal = None
        self.Capacity = None
        self.EngineMode = "serverless"
        self.HttpEndpointEnabled = False
        self.DeletionProtection = True
        self.TagList = [
            {"Key": "owner", "Value": "OPG Supervision"},
            {"Key": "environment-name", "Value": "development"},
            {"Key": "application", "Value": "Digideps"},
            {"Key": "is-production", "Value": "0"},
            {"Key": "business-unit", "Value": "OPG"},
            {
                "Key": "infrastructure-support",
                "Value": "OPG WebOps: opgteam@digital.justice.gov.uk",
            },
        ]
        self.AutoMinorVersionUpgrade = True

    def restore(self):
        if self.account is None:
            print("No valid account set! Exiting...")
            os.exit(1)
        self.create_digideps_client_session()

        if self.db_cluster_identifier_source is not None:
            self.get_cluster_info()
            if self.EngineMode != "serverless":
                self.get_instance_info()
            if (
                self.db_cluster_identifier_source == self.db_cluster_identifier_target
                and self.DeletionProtection
            ):
                self.drop_protection()

        self.apply_overrides()

        no_point_in_time_argument_set = datetime.strptime(
            "1900-01-01 00:00:00", "%Y-%m-%d %H:%M:%S"
        )

        self.KmsKeyIdLocal = self.get_kms_key(key_alias_local)

        if self.PointInTimeRecovery == no_point_in_time_argument_set:
            if self.SnapshotIdentifier is not None:
                if self.restore_from_remote:
                    self.create_backup_client_session()
                    self.KmsKeyId = self.get_kms_key(key_alias_remote)
                    self.share_snapshot_with_digideps()
                    self.copy_snapshot_to_manual_digideps()
                self.restore_from_snapshot()
            else:
                print(
                    "No snapshot specified. Either specify snapshot or do point in time recovery"
                )
        else:
            self.restore_to_point_in_time()

        print("Process has finished. Please go and check your databases")

    def drop_protection(self):
        response = self.client.modify_db_cluster(
            DeletionProtection=False,
            DBClusterIdentifier=self.db_cluster_identifier_source,
            ApplyImmediately=True,
        )

        self.command_response("Drop protection", response)

        self.wait_on_cluster_available(self.db_cluster_identifier_source)

    def get_instance_info(self):
        response = self.client.describe_db_instances(
            Filters=[
                {"Name": "db-cluster-id", "Values": [self.db_cluster_identifier_source]}
            ],
        )

        for instance in response["DBInstances"]:
            instance_obj = {
                "DBInstanceIdentifier": instance["DBInstanceIdentifier"],
                "DBInstanceClass": instance["DBInstanceClass"],
                "AvailabilityZone": instance["AvailabilityZone"],
            }
            self.instances.append(instance_obj)

        print(self.instances)

    def get_cluster_info(self):
        print(f"source is {self.db_cluster_identifier_source}")
        response = self.client.describe_db_clusters(
            DBClusterIdentifier=self.db_cluster_identifier_source
        )
        # Uncomment below print statement to debug all the RDS options available
        # print(response)
        self.AllocatedStorage = response["DBClusters"][0]["AllocatedStorage"]
        self.AvailabilityZones = response["DBClusters"][0]["AvailabilityZones"]
        self.BackupRetentionPeriod = response["DBClusters"][0]["BackupRetentionPeriod"]
        self.db_cluster_identifier_source = response["DBClusters"][0][
            "DBClusterIdentifier"
        ]
        self.DBClusterParameterGroup = response["DBClusters"][0][
            "DBClusterParameterGroup"
        ]
        self.DBSubnetGroup = response["DBClusters"][0]["DBSubnetGroup"]
        self.MultiAZ = response["DBClusters"][0]["MultiAZ"]
        self.Engine = response["DBClusters"][0]["Engine"]
        self.EngineVersion = response["DBClusters"][0]["EngineVersion"]
        self.VpcSecurityGroups = self.format_list(
            response["DBClusters"][0]["VpcSecurityGroups"], "VpcSecurityGroupId"
        )
        self.StorageEncrypted = response["DBClusters"][0]["StorageEncrypted"]
        self.KmsKeyId = response["DBClusters"][0]["KmsKeyId"]
        self.Capacity = (
            None
            if "Capacity" not in response["DBClusters"][0]
            else response["DBClusters"][0]["Capacity"]
        )
        self.EngineMode = response["DBClusters"][0]["EngineMode"]
        self.DeletionProtection = response["DBClusters"][0]["DeletionProtection"]
        self.TagList = response["DBClusters"][0]["TagList"]
        self.AutoMinorVersionUpgrade = response["DBClusters"][0][
            "AutoMinorVersionUpgrade"
        ]
        self.HttpEndpointEnabled = response["DBClusters"][0]["HttpEndpointEnabled"]
        if "ServerlessV2ScalingConfiguration" in response["DBClusters"][0]:
            if (
                "MinCapacity"
                in response["DBClusters"][0]["ServerlessV2ScalingConfiguration"]
            ):
                self.serverless_v2_config = {
                    "MinCapacity": response["DBClusters"][0][
                        "ServerlessV2ScalingConfiguration"
                    ]["MinCapacity"],
                    "MaxCapacity": response["DBClusters"][0][
                        "ServerlessV2ScalingConfiguration"
                    ]["MaxCapacity"],
                }

    def restore_from_snapshot(self):
        if self.same_target:
            self.db_cluster_identifier_target = (
                f"{self.db_cluster_identifier_source}-temp"
            )

        self.restore_cluster_snapshot()
        if self.EngineMode != "serverless":
            self.create_db_instances()

        if self.same_target:
            self.overwrite_existing_cluster()

    def restore_cluster_snapshot(self):
        if self.EngineMode == "serverless":
            response = self.client.restore_db_cluster_from_snapshot(
                DBClusterIdentifier=self.db_cluster_identifier_target,
                SnapshotIdentifier=self.SnapshotIdentifier,
                Engine=self.Engine,
                EngineVersion=self.EngineVersion,
                VpcSecurityGroupIds=self.VpcSecurityGroups,
                AutoMinorVersionUpgrade=self.AutoMinorVersionUpgrade,
                DBSubnetGroupName=self.DBSubnetGroup,
                DeletionProtection=self.DeletionProtection,
                EngineMode=self.EngineMode,
                KmsKeyId=self.KmsKeyIdLocal,
                Tags=self.TagList,
            )
        else:
            response = self.client.restore_db_cluster_from_snapshot(
                DBClusterIdentifier=self.db_cluster_identifier_target,
                SnapshotIdentifier=self.SnapshotIdentifier,
                ServerlessV2ScalingConfiguration=self.serverless_v2_config,
                Engine=self.Engine,
                EngineVersion=self.EngineVersion,
                VpcSecurityGroupIds=self.VpcSecurityGroups,
                DBSubnetGroupName=self.DBSubnetGroup,
                EnableCloudwatchLogsExports=["postgresql"],
                DeletionProtection=self.DeletionProtection,
                EngineMode=self.EngineMode,
                KmsKeyId=self.KmsKeyIdLocal,
                Tags=self.TagList,
            )

        self.command_response("Restore from snapshot", response)

        self.wait_on_cluster_available()

    def restore_to_point_in_time(self):
        if self.same_target:
            self.db_cluster_identifier_target = (
                f"{self.db_cluster_identifier_source}-temp"
            )

        self.restore_cluster_point_in_time_recovery()
        if self.EngineMode != "serverless":
            self.create_db_instances()

        if self.same_target:
            self.overwrite_existing_cluster()

    def restore_cluster_point_in_time_recovery(self):
        response = self.client.restore_db_cluster_to_point_in_time(
            DBClusterIdentifier=self.db_cluster_identifier_target,
            SourceDBClusterIdentifier=self.db_cluster_identifier_source,
            VpcSecurityGroupIds=self.VpcSecurityGroups,
            DBSubnetGroupName=self.DBSubnetGroup,
            RestoreToTime=self.PointInTimeRecovery,
            ServerlessV2ScalingConfiguration=self.serverless_v2_config,
            EnableCloudwatchLogsExports=["postgresql"],
            DeletionProtection=self.DeletionProtection,
            EngineMode=self.EngineMode,
            KmsKeyId=self.KmsKeyIdLocal,
            Tags=self.TagList,
        )

        self.command_response("Restore from PIT", response)

        self.wait_on_cluster_available()

    def create_db_instances(self):
        for instance in self.instances:
            instance_id = self.get_instance_id(instance)
            print(f"Creating instance {instance_id}")
            response = self.client.create_db_instance(
                DBClusterIdentifier=self.db_cluster_identifier_target,
                DBInstanceIdentifier=instance_id,
                AvailabilityZone=instance["AvailabilityZone"],
                DBInstanceClass=instance["DBInstanceClass"],
                AutoMinorVersionUpgrade=False,
                Engine=self.Engine,
            )
            self.command_response("Create instance", response)

        print("Waiting for instances to be created. This may take some time...")
        self.wait_on_instance_available(temp_instances=True)

    def overwrite_existing_cluster(self):
        source_backup = f"{self.db_cluster_identifier_source}-bck"
        self.rename_cluster(self.db_cluster_identifier_source, source_backup)
        self.wait_on_cluster_available(source_backup)

        if self.EngineMode != "serverless":
            self.delete_db_instances()
            self.rename_instances()

        self.rename_cluster(
            self.db_cluster_identifier_target, self.db_cluster_identifier_source
        )
        self.wait_on_cluster_available(self.db_cluster_identifier_source)
        self.delete_db_cluster(source_backup)

    def get_instance_id(self, instance, temp=True):
        return (
            f'{instance["DBInstanceIdentifier"]}-temp'
            if self.same_target and temp
            else instance["DBInstanceIdentifier"]
        )

    def rename_cluster(self, old_name, new_name):
        response = self.client.modify_db_cluster(
            DBClusterIdentifier=old_name,
            NewDBClusterIdentifier=new_name,
            ApplyImmediately=True,
        )

        self.command_response("Rename", response)

    def rename_instances(self):
        for instance in self.instances:
            response = self.client.modify_db_instance(
                DBInstanceIdentifier=f'{instance["DBInstanceIdentifier"]}-temp',
                NewDBInstanceIdentifier=instance["DBInstanceIdentifier"],
                ApplyImmediately=True,
            )

            self.command_response("Rename instance", response)

        self.wait_on_instance_available(temp_instances=False)

    def delete_db_cluster(self, cluster, skip_final_snapshot=True):
        if skip_final_snapshot:
            response = self.client.delete_db_cluster(
                DBClusterIdentifier=cluster, SkipFinalSnapshot=skip_final_snapshot
            )
        else:
            response = self.client.delete_db_cluster(
                DBClusterIdentifier=cluster,
                SkipFinalSnapshot=skip_final_snapshot,
                FinalDBSnapshotIdentifier=f"{cluster}-final-snapshot",
            )

        self.command_response("Delete", response)

        self.wait_on_cluster_deleted(cluster)

    def delete_db_instances(self):
        for instance in self.instances:
            response = self.client.delete_db_instance(
                DBInstanceIdentifier=instance["DBInstanceIdentifier"],
                SkipFinalSnapshot=True,
                DeleteAutomatedBackups=False,
            )
            self.command_response("Delete instance", response)

        self.wait_on_instances_deleted(temp_instances=False)

    def get_kms_key(self, key_alias):
        response = self.client_kms.describe_key(KeyId=str(key_alias))
        return response["KeyMetadata"]["Arn"]

    def apply_overrides(self):
        self.MultiAZ = (
            self.MultiAZ if self.multi_az_override is None else self.multi_az_override
        )

        if self.multi_az_override:
            self.instances = []
            for count, az in enumerate(self.AvailabilityZones):
                x = {
                    "DBInstanceIdentifier": f"{self.db_cluster_identifier_target}-{count}",
                    "DBInstanceClass": "db.t3.medium",
                    "AvailabilityZone": az,
                }
                self.instances.append(x)

        if self.engine_mode_override is not None:
            self.EngineMode = self.engine_mode_override
        elif self.EngineMode is not None:
            self.EngineMode = self.EngineMode
        else:
            self.EngineMode = "serverless"

    def wait_on_cluster_available(self, cluster=None):
        cluster_to_await = (
            self.db_cluster_identifier_target if cluster is None else cluster
        )
        print(f"Starting to wait on cluster {cluster_to_await}")
        total_time = 0
        sleep_time = 30
        while True:
            response = self.client.describe_db_clusters()
            cluster_from_response = [
                cluster
                for cluster in response["DBClusters"]
                if cluster["DBClusterIdentifier"] == cluster_to_await
            ]

            if len(cluster_from_response) > 0:
                status = cluster_from_response[0]["Status"]
                if status == "available":
                    print(
                        f"Cluster {cluster_to_await} exists but is not available yet. Status: {status}"
                    )
                    break
            else:
                print(f"Cluster {cluster_to_await} does not exist yet")
            time.sleep(sleep_time)
            total_time += sleep_time
            print(
                f"Waiting for cluster {cluster_to_await} to become available for {total_time} seconds"
            )

    def wait_on_cluster_deleted(self, cluster=None):
        cluster_to_await = (
            self.db_cluster_identifier_target if cluster is None else cluster
        )
        print(f"Starting to wait on cluster {cluster_to_await}")
        total_time = 0
        sleep_time = 30
        while True:
            response = self.client.describe_db_clusters()
            cluster_from_response = [
                cluster
                for cluster in response["DBClusters"]
                if cluster["DBClusterIdentifier"] == cluster_to_await
            ]

            if len(cluster_from_response) > 0:
                status = cluster_from_response[0]["Status"]
                print(f"Cluster {cluster_to_await} still exists. Status: {status}")
            else:
                print(
                    f"Cluster {cluster_to_await} no longer exists. Deletion successful"
                )
                break
            time.sleep(sleep_time)
            total_time += sleep_time
            print(
                f"Waiting for cluster {cluster_to_await} to be deleted for {total_time} seconds"
            )

    def wait_on_instance_available(self, temp_instances=True):
        print(f"Starting to wait on instances to become available")
        total_time = 0
        sleep_time = 30

        for instance in self.instances:
            instance_id = self.get_instance_id(instance, temp_instances)
            while True:
                response = self.client.describe_db_instances()
                instance_from_response = [
                    response_instance
                    for response_instance in response["DBInstances"]
                    if response_instance["DBInstanceIdentifier"] == instance_id
                ]

                if len(instance_from_response) > 0:
                    status = instance_from_response[0]["DBInstanceStatus"]
                    if status == "available":
                        print(
                            f"Instance {instance_id} exists but is not available yet. Status: {status}"
                        )
                        break
                else:
                    print(f"Instance {instance_id} does not exist yet")

                time.sleep(sleep_time)
                total_time += sleep_time
                print(
                    f"Waiting for instance {instance_id} to become available for {total_time} seconds"
                )

    def wait_on_instances_deleted(self, temp_instances=True):
        print(f"Starting to wait on instances to be deleted")
        total_time = 0
        sleep_time = 30
        for instance in self.instances:
            instance_id = self.get_instance_id(instance, temp_instances)
            while True:
                response = self.client.describe_db_instances()
                instance_from_response = [
                    response_instance
                    for response_instance in response["DBInstances"]
                    if response_instance["DBInstanceIdentifier"] == instance_id
                ]

                if len(instance_from_response) > 0:
                    status = instance_from_response[0]["DBInstanceStatus"]
                    print(f"Cluster {instance_id} still exists. Status: {status}")
                else:
                    print(
                        f"Cluster {instance_id} no longer exists. Deletion successful"
                    )
                    break
                time.sleep(sleep_time)
                total_time += sleep_time
                print(
                    f"Waiting for instance {instance_id} to be deleted for {total_time} seconds"
                )

    def share_snapshot_with_digideps(self):
        print(
            f"Sharing snapshot {self.SnapshotIdentifier} with account {self.account}..."
        )
        self.client_backup_rds.modify_db_cluster_snapshot_attribute(
            AttributeName="restore",
            DBClusterSnapshotIdentifier=self.SnapshotIdentifier,
            ValuesToAdd=[self.account],
            ValuesToRemove=["all"],
        )
        print(f"Snapshot {self.SnapshotIdentifier} shared with account {self.account}")

    def share_snapshot_with_backup(self):
        print(
            f"Sharing snapshot {self.SnapshotIdentifier} with account {self.backup_account}..."
        )
        target = str(self.SnapshotIdentifier.split(":")[1])
        self.client.modify_db_cluster_snapshot_attribute(
            AttributeName="restore",
            DBClusterSnapshotIdentifier=target,
            ValuesToAdd=[self.backup_account],
            ValuesToRemove=["all"],
        )
        print(
            f"Snapshot {self.SnapshotIdentifier} shared with account {self.backup_account}"
        )

    def copy_snapshot_to_manual_digideps(self):
        source = f"arn:aws:rds:{self.region}:{self.backup_account}:cluster-snapshot:{self.SnapshotIdentifier}"
        target = self.SnapshotIdentifier
        self.client.copy_db_cluster_snapshot(
            SourceDBClusterSnapshotIdentifier=source,
            TargetDBClusterSnapshotIdentifier=target,
            KmsKeyId=self.KmsKeyId,
            SourceRegion="eu-west-1",
        )
        print(f"Copying {self.SnapshotIdentifier} to {target}...")

        self.wait_snapshot_copy_finish(self.client, target)

    def copy_snapshot_to_manual_backup(self):
        target = str(self.SnapshotIdentifier.split(":")[1])
        source = f"arn:aws:rds:{self.region}:{self.account}:cluster-snapshot:{target}"
        self.client_backup_rds.copy_db_cluster_snapshot(
            SourceDBClusterSnapshotIdentifier=source,
            TargetDBClusterSnapshotIdentifier=target,
            KmsKeyId=self.KmsKeyId,
            SourceRegion="eu-west-1",
        )
        print(f"Copying {source} to {target}...")

        self.wait_snapshot_copy_finish(self.client_backup_rds, target)

    @staticmethod
    def wait_snapshot_copy_finish(client, target_snapshot_id):
        status = "none"
        secs = 0
        timeout = 7200
        while status != "available" and secs < timeout:
            manual_snapshot = client.describe_db_cluster_snapshots(
                SnapshotType="manual", DBClusterSnapshotIdentifier=target_snapshot_id
            )
            status = manual_snapshot["DBClusterSnapshots"][0]["Status"]

            secs += 10
            time.sleep(10)
            print(f"Copying {target_snapshot_id}: {secs} seconds elapsed")

        return True if secs < timeout else False

    def refresh_creds_digideps(self):
        "Refresh tokens by calling assume_role again"
        params = {
            "RoleArn": self.role_to_assume,
            "RoleSessionName": "digideps_restores",
            "DurationSeconds": 900,
        }

        response = self.sts_client.assume_role(**params).get("Credentials")
        credentials = {
            "access_key": response.get("AccessKeyId"),
            "secret_key": response.get("SecretAccessKey"),
            "token": response.get("SessionToken"),
            "expiry_time": response.get("Expiration").isoformat(),
        }
        return credentials

    def create_digideps_client_session(self):
        session_credentials = RefreshableCredentials.create_from_metadata(
            metadata=self.refresh_creds_digideps(),
            refresh_using=self.refresh_creds_digideps,
            method="sts-assume-role",
        )
        session = get_session()
        session._credentials = session_credentials
        session.set_config_variable("region", self.region)
        autorefresh_session = boto3.Session(botocore_session=session)
        self.client = autorefresh_session.client("rds", region_name=self.region)

        self.client_kms = autorefresh_session.client("kms", region_name=self.region)

    def refresh_creds_backup(self):
        "Refresh tokens by calling assume_role again"
        params = {
            "RoleArn": self.backup_role_to_assume,
            "RoleSessionName": "digideps_restores",
            "DurationSeconds": 900,
        }

        response = self.sts_client.assume_role(**params).get("Credentials")
        credentials = {
            "access_key": response.get("AccessKeyId"),
            "secret_key": response.get("SecretAccessKey"),
            "token": response.get("SessionToken"),
            "expiry_time": response.get("Expiration").isoformat(),
        }
        return credentials

    def create_backup_client_session(self):
        session_credentials = RefreshableCredentials.create_from_metadata(
            metadata=self.refresh_creds_backup(),
            refresh_using=self.refresh_creds_backup,
            method="sts-assume-role",
        )
        session = get_session()
        session._credentials = session_credentials
        session.set_config_variable("region", self.region)
        autorefresh_session = boto3.Session(botocore_session=session)

        self.client_backup_rds = autorefresh_session.client(
            "rds", region_name=self.region
        )

        self.client_backup_kms = autorefresh_session.client(
            "kms", region_name=self.region
        )

    @staticmethod
    def command_response(command, response):
        print(
            f'{command} command responded with status: {response["ResponseMetadata"]["HTTPStatusCode"]}'
        )

    @staticmethod
    def format_list(list, identifier):
        output_list = []
        for i in list:
            output_list.append(str(i[identifier]))
        return output_list


@click.command()
@click.option(
    "--environment",
    default="development",
    help="Environment to restore into (development/preproduction/production)",
)
@click.option(
    "--cluster_from",
    default=None,
    help="Cluster for getting the settings from and for point in time restores",
)
@click.option(
    "--cluster_to",
    default=None,
    help="Cluster to restore into. Leave blank if same as cluster_from",
)
@click.option(
    "--snapshot_id",
    default=None,
    help="Snapshot to use for the restore (not needed for point in time recovery)",
)
@click.option(
    "--pitr",
    default="1900-01-01 00:00:00",
    help="Date for Point in Time Recovery (default means don't use pitr)",
)
@click.option(
    "--multi_az_override",
    default=None,
    help="Make restored cluster a different az spec than original",
)
@click.option(
    "--engine_mode_override",
    default=None,
    help="Make restored cluster a different engine mode than original",
)
@click.option(
    "--restore_from_remote", default=False, help="Whether to restore from remote backup"
)
def main(
    environment,
    cluster_from,
    cluster_to,
    snapshot_id,
    multi_az_override,
    engine_mode_override,
    pitr,
    restore_from_remote,
):
    dr_job = SnapshotManagement(
        environment=environment,
        cluster_from=cluster_from,
        cluster_to=cluster_to,
        snapshot_id=snapshot_id,
        multi_az_override=multi_az_override,
        engine_mode_override=engine_mode_override,
        pitr=pitr,
        restore_from_remote=restore_from_remote,
    )
    dr_job.restore()


if __name__ == "__main__":
    main()
