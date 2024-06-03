import boto3


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
    return boto3.Session(**create_session_kwargs)


def filter_none_values(kwargs: dict) -> dict:
    """Returns a new dictionary excluding items where value was None"""
    return {k: v for k, v in kwargs.items() if v is not None}


def get_latest_snapshot(rds_client, cid, cluster=True):
    if cluster:
        automated_snapshots = rds_client.describe_db_cluster_snapshots(
            DBClusterIdentifier=cid, SnapshotType="manual"
        )
    else:
        automated_snapshots = rds_client.describe_db_snapshots(
            DBInstanceIdentifier=cid, SnapshotType="manual"
        )

    snapshots = []
    if cluster:
        for snapshot in automated_snapshots["DBClusterSnapshots"]:
            snapshots.append(str(snapshot["DBClusterSnapshotIdentifier"]))
    else:
        for snapshot in automated_snapshots["DBSnapshots"]:
            snapshots.append(str(snapshot["DBSnapshotIdentifier"]))

    snapshots.sort(reverse=True)
    snapshot_id = snapshots[0]

    return snapshot_id


def main():
    aws_region = "eu-west-1"
    backup_account = "238302996107"
    backup_role = "cross-acc-db-restore.digideps-development"
    backup_acc_role_arn = f"arn:aws:iam::{backup_account}:role/{backup_role}"

    backup_session = assume_session(
        "restore-get-latest-snapshot", backup_acc_role_arn, aws_region
    )

    backup_client = backup_session.client("rds", region_name=aws_region)
    snapshot = get_latest_snapshot(backup_client, "api-integration", cluster=True)
    print(snapshot)


if __name__ == "__main__":
    main()
