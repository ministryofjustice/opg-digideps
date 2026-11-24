#!/usr/bin/env python3

import argparse
import json
from urllib.parse import unquote

import boto3

iam = boto3.client("iam")

LOCKDOWN_POLICY_NAME = "EmergencyLockdown"

EXCLUDED_ROLES = {
    "breakglass",
    "operator",
    "viewer",
}

LOCKDOWN_POLICY = {
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "EmergencyLockdown",
            "Effect": "Deny",
            "Action": "*",
            "Resource": "*",
        }
    ],
}


def get_roles():
    paginator = iam.get_paginator("list_roles")

    for page in paginator.paginate():
        for role_summary in page["Roles"]:
            role = iam.get_role(RoleName=role_summary["RoleName"])["Role"]

            if role["Path"].startswith("/aws-service-role/"):
                continue

            yield role


def normalise_principals(principal):
    if isinstance(principal, str):
        return [principal]

    if isinstance(principal, list):
        return principal

    return []


def extract_principals(trust_policy):
    principals = []

    for statement in trust_policy.get("Statement", []):
        principal = statement.get("Principal")

        if principal == "*":
            principals.append("*")
            continue

        if not isinstance(principal, dict):
            continue

        for principal_type, values in principal.items():
            for value in normalise_principals(values):
                principals.append(
                    {
                        "type": principal_type,
                        "value": value,
                    }
                )

    return principals


def get_boundary_name(role):
    boundary = role.get("PermissionsBoundary")

    if not boundary:
        return None

    arn = boundary["PermissionsBoundaryArn"]
    return arn.split("/")[-1]


def collect_data():
    roles = []
    boundaries = {}

    for role in sorted(get_roles(), key=lambda r: r["RoleName"]):
        roles.append(role)

        boundary = role.get("PermissionsBoundary")
        if boundary:
            arn = boundary["PermissionsBoundaryArn"]
            boundaries[arn.split("/")[-1]] = arn

    return roles, boundaries


def print_report(roles, boundaries):
    print()
    print("=" * 80)
    print("SUMMARY")
    print("=" * 80)
    print()

    print(f"Roles discovered: {len(roles)}")
    print(f"Unique boundaries: {len(boundaries)}")
    print()

    if boundaries:
        print("Boundaries:")
        for name in sorted(boundaries):
            print(f"  {name}")
        print()

    for role in roles:
        role_name = role["RoleName"]

        trust_policy = role["AssumeRolePolicyDocument"]

        if isinstance(trust_policy, str):
            trust_policy = json.loads(unquote(trust_policy))

        boundary_name = get_boundary_name(role)

        print("=" * 80)
        print(f"Role: {role_name}")
        print(f"Boundary: {boundary_name or 'NONE'}")
        print()

        principals = extract_principals(trust_policy)

        if not principals:
            print("Trusted Principals: NONE")
            print()
            continue

        print("Trusted Principals:")

        for principal in principals:
            if principal == "*":
                print("  *")
                continue

            value = principal["value"]

            if ":user/" in value:
                principal_kind = "USER"
            elif ":role/" in value:
                principal_kind = "ROLE"
            elif value.endswith(":root"):
                principal_kind = "ACCOUNT_ROOT"
            else:
                principal_kind = principal["type"]

            print(f"  [{principal_kind}] {value}")

        print()


def role_is_excluded(role_name):
    return role_name.lower() in EXCLUDED_ROLES


def put_lockdown_policy(role_name, execute=False):
    print(f"LOCK  {role_name}")

    if not execute:
        return

    iam.put_role_policy(
        RoleName=role_name,
        PolicyName=LOCKDOWN_POLICY_NAME,
        PolicyDocument=json.dumps(LOCKDOWN_POLICY),
    )


def remove_lockdown_policy(role_name, execute=False):
    print(f"UNLOCK {role_name}")

    if not execute:
        return

    try:
        iam.delete_role_policy(
            RoleName=role_name,
            PolicyName=LOCKDOWN_POLICY_NAME,
        )
    except iam.exceptions.NoSuchEntityException:
        pass


def lock_all(roles, execute=False):
    for role in roles:
        role_name = role["RoleName"]

        if role_is_excluded(role_name):
            print(f"SKIP  {role_name}")
            continue

        put_lockdown_policy(role_name, execute)


def unlock_all(roles, execute=False):
    for role in roles:
        remove_lockdown_policy(
            role["RoleName"],
            execute,
        )


def get_role(role_name):
    return iam.get_role(RoleName=role_name)["Role"]


def lock_role(role_name, execute=False):
    get_role(role_name)

    put_lockdown_policy(
        role_name,
        execute=execute,
    )


def unlock_role(role_name, execute=False):
    get_role(role_name)

    remove_lockdown_policy(role_name, execute)


def main():
    parser = argparse.ArgumentParser()

    subparsers = parser.add_subparsers(
        dest="command",
        required=True,
    )

    subparsers.add_parser("report")
    subparsers.add_parser("lock-all")
    subparsers.add_parser("unlock-all")

    p = subparsers.add_parser("lock-role")
    p.add_argument("role")

    p = subparsers.add_parser("unlock-role")
    p.add_argument("role")

    parser.add_argument(
        "--execute",
        action="store_true",
        help="Actually make changes",
    )

    args = parser.parse_args()

    if args.command == "report":
        roles, boundaries = collect_data()
        print_report(roles, boundaries)

    elif args.command == "lock-all":
        roles, boundaries = collect_data()
        lock_all(roles, execute=args.execute)

    elif args.command == "unlock-all":
        roles, boundaries = collect_data()
        unlock_all(roles, execute=args.execute)

    elif args.command == "lock-role":
        lock_role(
            args.role,
            execute=args.execute,
        )

    elif args.command == "unlock-role":
        unlock_role(
            args.role,
            execute=args.execute,
        )


if __name__ == "__main__":
    main()
