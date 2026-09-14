#!/usr/bin/env python3

import argparse
import json
from datetime import datetime, timedelta, timezone

import boto3

# Example Usage: python grant_s3_access.py pa-uploads-preproduction data_request.csv
# When to use: For specific time limited and signed off work extracting non PII data.


def build_policy(bucket_name: str, object_key: str, expires: datetime) -> dict:
    """Build a temporary IAM inline policy for a single S3 object."""

    return {
        "Version": "2012-10-17",
        "Statement": [
            {
                "Sid": "TemporaryObjectAccess",
                "Effect": "Allow",
                "Action": [
                    "s3:GetObject",
                    "s3:PutObject",
                ],
                "Resource": f"arn:aws:s3:::{bucket_name}/{object_key}",
                "Condition": {
                    "DateLessThan": {
                        "aws:CurrentTime": expires.strftime("%Y-%m-%dT%H:%M:%SZ")
                    }
                },
            }
        ],
    }


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Grant temporary access to a specific S3 object using an inline IAM policy."
    )

    parser.add_argument(
        "bucket_name",
        help="Name of the S3 bucket",
    )

    parser.add_argument(
        "object_key",
        help="S3 object key",
    )

    return parser.parse_args()


def main() -> None:
    args = parse_args()
    role = "data-access"
    policy = "grant-data-access-s3-access"

    expires = datetime.now(timezone.utc) + timedelta(hours=2)

    policy_document = build_policy(
        bucket_name=args.bucket_name,
        object_key=args.object_key,
        expires=expires,
    )

    iam = boto3.client("iam")

    iam.put_role_policy(
        RoleName=role,
        PolicyName=policy,
        PolicyDocument=json.dumps(policy_document),
    )

    print(
        f"Attached policy '{policy}' to role '{role}' "
        f"for s3://{args.bucket_name}/{args.object_key} "
        f"until {expires.isoformat()}"
    )


if __name__ == "__main__":
    main()
