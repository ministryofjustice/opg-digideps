#!/usr/bin/env python3

import argparse

import boto3

REGION = "eu-west-1"


class KmsLockdown:
    def __init__(self) -> None:
        self.kms = boto3.client(
            "kms",
            region_name=REGION,
        )

    def list_keys(self) -> list[str]:
        paginator = self.kms.get_paginator("list_keys")

        keys = []

        for page in paginator.paginate():
            keys.extend(page["Keys"])

        return [k["KeyArn"] for k in keys]

    def get_aliases(self) -> dict[str, list[str]]:
        paginator = self.kms.get_paginator("list_aliases")

        aliases: dict[str, list[str]] = {}

        for page in paginator.paginate():
            for alias in page["Aliases"]:
                target_key_id = alias.get("TargetKeyId")

                if not target_key_id:
                    continue

                aliases.setdefault(
                    target_key_id,
                    [],
                ).append(alias["AliasName"])

        return aliases

    def get_key_metadata(self, key_id: str) -> dict:
        return self.kms.describe_key(
            KeyId=key_id,
        )["KeyMetadata"]

    def is_customer_managed(self, metadata: dict) -> bool:
        return metadata["KeyManager"] == "CUSTOMER"

    def report(self) -> None:
        print("\nKMS Keys\n")

        aliases = self.get_aliases()

        for key_arn in self.list_keys():
            metadata = self.get_key_metadata(key_arn)

            key_aliases = aliases.get(
                metadata["KeyId"],
                [],
            )

            alias_text = ", ".join(sorted(key_aliases)) if key_aliases else "-"

            print(
                f"{metadata['KeyState']:10} "
                f"{metadata['KeyManager']:8} "
                f"{alias_text:40} "
                f"{key_arn}"
            )

    def disable_key(self, key_arn: str) -> None:
        metadata = self.get_key_metadata(key_arn)

        if not self.is_customer_managed(metadata):
            print(f"Skipping AWS-managed key: {key_arn}")
            return

        if metadata["KeyState"] == "Disabled":
            print(f"Already disabled: {key_arn}")
            return

        print(f"Disabling {key_arn}")

        self.kms.disable_key(
            KeyId=key_arn,
        )

    def enable_key(self, key_arn: str) -> None:
        metadata = self.get_key_metadata(key_arn)

        if not self.is_customer_managed(metadata):
            print(f"Skipping AWS-managed key: {key_arn}")
            return

        if metadata["KeyState"] == "Enabled":
            print(f"Already enabled: {key_arn}")
            return

        print(f"Enabling {key_arn}")

        self.kms.enable_key(
            KeyId=key_arn,
        )

    def lock_all(self) -> None:
        for key_arn in self.list_keys():
            self.disable_key(key_arn)

    def unlock_all(self) -> None:
        for key_arn in self.list_keys():
            self.enable_key(key_arn)


def main() -> None:
    parser = argparse.ArgumentParser(
        description="Emergency KMS lockdown utility",
    )

    subparsers = parser.add_subparsers(
        dest="command",
        required=True,
    )

    subparsers.add_parser("report")
    subparsers.add_parser("lock-all")
    subparsers.add_parser("unlock-all")

    lock_key = subparsers.add_parser("lock-key")
    lock_key.add_argument("key_arn")

    unlock_key = subparsers.add_parser("unlock-key")
    unlock_key.add_argument("key_arn")

    args = parser.parse_args()

    kms = KmsLockdown()

    if args.command == "report":
        kms.report()

    elif args.command == "lock-all":
        kms.lock_all()

    elif args.command == "unlock-all":
        kms.unlock_all()

    elif args.command == "lock-key":
        kms.disable_key(args.key_arn)

    elif args.command == "unlock-key":
        kms.enable_key(args.key_arn)


if __name__ == "__main__":
    main()
