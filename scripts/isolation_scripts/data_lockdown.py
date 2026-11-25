#!/usr/bin/env python3

import argparse
import json
from datetime import datetime

import boto3
from botocore.exceptions import ClientError

REGION = "eu-west-1"


class Lockdown:
    def __init__(self, environment: str):
        self.environment = environment

        self.bucket_name = f"pa-uploads-{environment}"
        self.db_cluster_id = f"digideps-{environment}"

        self.state_file = f"lockdown-state-{environment}.json"

        self.s3 = boto3.client(
            "s3",
            region_name=REGION,
        )

        self.rds = boto3.client(
            "rds",
            region_name=REGION,
        )

        self.sts = boto3.client(
            "sts",
            region_name=REGION,
        )

        self.account_id = self.sts.get_caller_identity()["Account"]

        self.breakglass_role_arn = f"arn:aws:iam::{self.account_id}:role/breakglass"

    def build_lockdown_policy(self) -> dict:
        return {
            "Version": "2012-10-17",
            "Statement": [
                {
                    "Sid": "EmergencyDataLockdown",
                    "Effect": "Deny",
                    "NotPrincipal": {"AWS": self.breakglass_role_arn},
                    "Action": ["s3:GetObject", "s3:GetObjectVersion"],
                    "Resource": f"arn:aws:s3:::{self.bucket_name}/*",
                }
            ],
        }

    def save_state(self, state: dict) -> None:
        with open(self.state_file, "w") as f:
            json.dump(state, f, indent=2)

    def load_state(self) -> dict:
        with open(self.state_file) as f:
            return json.load(f)

    def get_bucket_policy(self) -> str | None:
        try:
            response = self.s3.get_bucket_policy(
                Bucket=self.bucket_name,
            )
            return response["Policy"]

        except ClientError as exc:
            if exc.response["Error"]["Code"] == "NoSuchBucketPolicy":
                return None

            raise

    def apply_lockdown_policy(self) -> None:
        policy = self.build_lockdown_policy()

        self.s3.put_bucket_policy(
            Bucket=self.bucket_name,
            Policy=json.dumps(policy),
        )

    def lockdown(self) -> None:
        state = {
            "timestamp": datetime.utcnow().isoformat(),
            "environment": self.environment,
            "bucket": self.bucket_name,
            "cluster": self.db_cluster_id,
            "original_bucket_policy": self.get_bucket_policy(),
        }

        self.save_state(state)

        print(f"Applying lockdown policy to " f"{self.bucket_name}")

        self.apply_lockdown_policy()

        print(f"Stopping Aurora cluster " f"{self.db_cluster_id}")

        self.rds.stop_db_cluster(
            DBClusterIdentifier=self.db_cluster_id,
        )

        print("Lockdown complete")

    def restore(self) -> None:
        state = self.load_state()

        policy = state["original_bucket_policy"]

        if policy:
            print(f"Restoring bucket policy " f"for {self.bucket_name}")

            self.s3.put_bucket_policy(
                Bucket=self.bucket_name,
                Policy=policy,
            )

        else:
            print(f"Removing bucket policy " f"for {self.bucket_name}")

            try:
                self.s3.delete_bucket_policy(
                    Bucket=self.bucket_name,
                )
            except ClientError:
                pass

        print(f"Starting Aurora cluster " f"{self.db_cluster_id}")

        self.rds.start_db_cluster(
            DBClusterIdentifier=self.db_cluster_id,
        )

        print("Restore complete")


def main() -> None:
    parser = argparse.ArgumentParser(
        description="Emergency Digideps lockdown utility",
    )

    parser.add_argument(
        "action",
        choices=["lockdown", "restore"],
    )

    parser.add_argument(
        "environment",
        help="e.g. development, staging, production",
    )

    args = parser.parse_args()

    lockdown = Lockdown(args.environment)

    if args.action == "lockdown":
        lockdown.lockdown()
    else:
        lockdown.restore()


if __name__ == "__main__":
    main()
