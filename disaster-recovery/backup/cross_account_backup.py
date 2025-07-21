import boto3
import time
import os


def delete_snapshot(rds_client, id, cluster=True):
    snapshot_type = "manual"
    try:
        if cluster:
            rds_client.describe_db_cluster_snapshots(
                DBClusterSnapshotIdentifier=id, SnapshotType=snapshot_type
            )
        else:
            rds_client.describe_db_snapshots(
                DBSnapshotIdentifier=id, SnapshotType=snapshot_type
            )
        print(f"Snapshot {id} exists, deleting...")

        if cluster:
            rds_client.delete_db_cluster_snapshot(DBClusterSnapshotIdentifier=id)
        else:
            rds_client.delete_db_snapshot(DBSnapshotIdentifier=id)

        exists = True
        secs = 0
        while exists:
            time.sleep(10)
            secs += 10
            print(f"Deleting {id}, {secs} seconds elapsed")
            try:
                if cluster:
                    rds_client.describe_db_cluster_snapshots(
                        DBClusterSnapshotIdentifier=id, SnapshotType=snapshot_type
                    )
                else:
                    rds_client.describe_db_snapshots(
                        DBSnapshotIdentifier=id, SnapshotType=snapshot_type
                    )
            except:
                exists = False
                print("Finished deleting")

    except:
        print(f"Snapshot {id} does not exist")


def get_latest_snapshot(rds_client, cid, cluster=True):
    if cluster:
        automated_snapshots = rds_client.describe_db_cluster_snapshots(
            DBClusterIdentifier=cid, SnapshotType="automated"
        )
        snapshot_key = "DBClusterSnapshots"
        id_key = "DBClusterSnapshotIdentifier"
    else:
        automated_snapshots = rds_client.describe_db_snapshots(
            DBInstanceIdentifier=cid, SnapshotType="automated"
        )
        snapshot_key = "DBSnapshots"
        id_key = "DBSnapshotIdentifier"

    snapshots = []

    for snapshot in automated_snapshots.get(snapshot_key, []):
        snapshot_identifier = str(snapshot.get(id_key, ""))
        # preupgrade is a standard system snapshot we want to exclude
        if "preupgrade" not in snapshot_identifier:
            snapshots.append(snapshot_identifier)

    snapshots.sort(reverse=True)
    snapshot_id = snapshots[0]
    print(f"Latest snapshot is {snapshot_id}")

    return snapshot_id


def get_snapshots_to_delete(rds_client, cid, keep_count, cluster=True):
    if cluster:
        manual_snapshots = rds_client.describe_db_cluster_snapshots(
            DBClusterIdentifier=cid, SnapshotType="manual"
        )
    else:
        manual_snapshots = rds_client.describe_db_snapshots(
            DBInstanceIdentifier=cid, SnapshotType="manual"
        )
    snapshots = []
    if cluster:
        for snapshot in manual_snapshots["DBClusterSnapshots"]:
            snapshots.append(str(snapshot["DBClusterSnapshotIdentifier"]))
    else:
        for snapshot in manual_snapshots["DBSnapshots"]:
            snapshots.append(str(snapshot["DBSnapshotIdentifier"]))

    snapshots.sort(reverse=True)
    snapshots_to_delete = []
    count = 0
    for snapshot in snapshots:
        count += 1
        if count > keep_count:
            snapshots_to_delete.append(snapshot)

    return snapshots_to_delete


def wait_finish_copy(rds_client, target_snapshot_id, cluster=True):
    status = "none"
    secs = 0
    timeout = 7200
    while status != "available" and secs < timeout:
        if cluster:
            manual_snapshot = rds_client.describe_db_cluster_snapshots(
                SnapshotType="manual", DBClusterSnapshotIdentifier=target_snapshot_id
            )
            status = manual_snapshot["DBClusterSnapshots"][0]["Status"]
        else:
            manual_snapshot = rds_client.describe_db_snapshots(
                SnapshotType="manual", DBSnapshotIdentifier=target_snapshot_id
            )
            status = manual_snapshot["DBSnapshots"][0]["Status"]

        secs += 10
        time.sleep(10)
        print(f"Copying {target_snapshot_id}: {secs} seconds elapsed")

    if secs < timeout:
        return True
    else:
        return False


def copy_latest_snapshot(
    rds_client, snapshot_id, target_snapshot_id, kms, cluster=True
):
    if cluster:
        rds_client.copy_db_cluster_snapshot(
            SourceDBClusterSnapshotIdentifier=snapshot_id,
            TargetDBClusterSnapshotIdentifier=target_snapshot_id,
            KmsKeyId=kms,
            SourceRegion="eu-west-1",
        )
    else:
        rds_client.copy_db_snapshot(
            SourceDBSnapshotIdentifier=snapshot_id,
            TargetDBSnapshotIdentifier=target_snapshot_id,
            KmsKeyId=kms,
            SourceRegion="eu-west-1",
        )
    print(f"Copying {snapshot_id} to {target_snapshot_id}...")

    if wait_finish_copy(rds_client, target_snapshot_id, cluster):
        print(f"Finished copying {target_snapshot_id}")
    else:
        print(f"Copy timed out")


def copy_individual_snapshot(
    rds_client, source_id, target_id, kms_id, region, cluster=True
):
    print(f"Copying from {source_id} to {target_id}...")
    if cluster:
        rds_client.copy_db_cluster_snapshot(
            SourceDBClusterSnapshotIdentifier=source_id,
            TargetDBClusterSnapshotIdentifier=target_id,
            KmsKeyId=kms_id,
            SourceRegion=region,
        )
    else:
        rds_client.copy_db_snapshot(
            SourceDBSnapshotIdentifier=source_id,
            TargetDBSnapshotIdentifier=target_id,
            KmsKeyId=kms_id,
            SourceRegion=region,
        )
    if wait_finish_copy(rds_client, target_id, cluster):
        print(f"Finished copying {target_id}")
    else:
        print(f"Copy timed out")


def share_snapshot(rds_client, snapshot_id, account, cluster=True):
    print(f"Sharing snapshot {snapshot_id} with account {account}...")
    if cluster:
        rds_client.modify_db_cluster_snapshot_attribute(
            AttributeName="restore",
            DBClusterSnapshotIdentifier=snapshot_id,
            ValuesToAdd=[account],
            ValuesToRemove=[
                "all",
            ],
        )
    else:
        rds_client.modify_db_snapshot_attribute(
            AttributeName="restore",
            DBSnapshotIdentifier=snapshot_id,
            ValuesToAdd=[account],
            ValuesToRemove=[
                "all",
            ],
        )
    print(f"Snapshot {snapshot_id} shared with account {account}")


def filter_none_values(kwargs: dict) -> dict:
    """Returns a new dictionary excluding items where value was None"""
    return {k: v for k, v in kwargs.items() if v is not None}


def assume_session(
    role_session_name: str,
    role_arn: str,
    region_name: str,
    duration_seconds: int = None,
) -> boto3.Session:
    """
    Returns a session with the given name and role.
    If not specified, duration will be set by AWS, probably at 1 hour.
    Region can be overridden by each client or resource spawned from this session.
    """
    assume_role_kwargs = filter_none_values(
        {
            "RoleSessionName": role_session_name,
            "RoleArn": role_arn,
            "DurationSeconds": duration_seconds,
        }
    )
    credentials = boto3.client("sts").assume_role(**assume_role_kwargs)["Credentials"]
    create_session_kwargs = filter_none_values(
        {
            "aws_access_key_id": credentials["AccessKeyId"],
            "aws_secret_access_key": credentials["SecretAccessKey"],
            "aws_session_token": credentials["SessionToken"],
            "region_name": region_name,
        }
    )
    print(f"Assumed role {role_arn}")
    return boto3.Session(**create_session_kwargs)


def str_to_bool(v):
    return v.lower() in ("yes", "true", "t", "1")


def main():
    aws_region = "eu-west-1"
    cluster_id = os.environ["DB_ID"]
    source_account = os.environ["SOURCE_ACCOUNT"]
    kms_key_id = os.environ["KMS_KEY_ID"]
    shared_kms_key_id = f"arn:aws:kms:{aws_region}:{source_account}:key/{kms_key_id}"
    backup_account = os.environ["BACKUP_ACCOUNT"]
    backup_acc_role_arn = os.environ["BACKUP_ACCOUNT_ROLE"]
    cluster_bool = os.environ["CLUSTER"]
    cluster = str_to_bool(cluster_bool)
    backups_to_keep = 7

    client = boto3.client("rds", region_name=aws_region)

    print(f"Database to backup {cluster_id}")

    try:
        snapshot_identifier = get_latest_snapshot(client, cluster_id, cluster)
        target_snapshot_identifier = snapshot_identifier.replace("rds:", "")

        if cluster:
            shared_snapshot_identifier = f"arn:aws:rds:{aws_region}:{source_account}:cluster-snapshot:{target_snapshot_identifier}"
        else:
            shared_snapshot_identifier = f"arn:aws:rds:{aws_region}:{source_account}:snapshot:{target_snapshot_identifier}"

        backup_snapshot_identifier = f"{target_snapshot_identifier}-bck"

        # Delete target if it exists
        delete_snapshot(client, target_snapshot_identifier, cluster)

        # Copy snapshot over to manual using KMS key
        copy_latest_snapshot(
            client, snapshot_identifier, target_snapshot_identifier, kms_key_id, cluster
        )

        # Share the snapshot with sandbox
        share_snapshot(client, target_snapshot_identifier, backup_account, cluster)

        backup_session = assume_session(
            "backup_session", backup_acc_role_arn, aws_region
        )

        backup_client = backup_session.client("rds", region_name=aws_region)

        # Delete backup acc target if it exists
        delete_snapshot(backup_client, backup_snapshot_identifier, cluster)

        copy_individual_snapshot(
            backup_client,
            shared_snapshot_identifier,
            backup_snapshot_identifier,
            shared_kms_key_id,
            aws_region,
            cluster,
        )

        # Delete shared manual backup
        delete_snapshot(client, target_snapshot_identifier, cluster)

        print("Deleting old backups...")
        for snapshot in get_snapshots_to_delete(
            backup_client, cluster_id, backups_to_keep, cluster
        ):
            print(f"Found {snapshot} to delete")
            delete_snapshot(backup_client, snapshot, cluster)

        print(
            "cross_account_backup - success - Finished processing cross account DR backups"
        )

    except Exception:
        print(
            "cross_account_backup - failure - Error processing cross account DR backups"
        )


if __name__ == "__main__":
    main()
