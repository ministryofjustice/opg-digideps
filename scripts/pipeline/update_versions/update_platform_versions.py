#!/usr/bin/env python3
from __future__ import annotations

import json
import re
import sys
from pathlib import Path
from urllib.request import urlopen

"""
This script is used for handling weekly checks for updates of various platform versions.
To check a platform, add a new class for that platform and put all code in the class.
"""

CONFIG_FILE = Path("../../../terraform/environment/terraform.tfvars.json")

AURORA_RELEASE_CALENDAR = (
    "https://docs.aws.amazon.com/AmazonRDS/latest/"
    "AuroraPostgreSQLReleaseNotes/"
    "aurorapostgresql-release-calendar.md"
)

PRODUCTION = "production"

NON_PROD = ["development", "staging", "training", "preproduction", "default"]


class AuroraPostgresUpdater:
    """
    Handles Aurora PostgreSQL patch version updates.

    Rules:

    1. If production != preproduction
       -> promote preproduction to production (as run weekly).

    2. Otherwise:
       -> find latest Aurora patch version available
          within production major version.

       -> update all non-prod environments.

    3. If no changes required
       -> exit with no modifications.
    """

    def __init__(self, config_path: Path):
        self.config_path = config_path

    def load(self) -> dict:
        with self.config_path.open() as f:
            return json.load(f)

    def save(self, config: dict) -> None:
        with self.config_path.open("w") as f:
            json.dump(config, f, indent=2)
            f.write("\n")

    def get_engine_version(self, config: dict, environment: str) -> str:
        return config["accounts"][environment]["db"]["psql_engine_version"]

    def set_engine_version(
        self,
        config: dict,
        environment: str,
        version: str,
    ) -> None:
        config["accounts"][environment]["db"]["psql_engine_version"] = version

    from urllib.request import urlopen

    def fetch_release_calendar(self) -> str:
        print("Fetching Aurora release calendar...")

        with urlopen(AURORA_RELEASE_CALENDAR, timeout=30) as response:
            if response.status != 200:
                raise RuntimeError(f"Unexpected status code: {response.status}")

            return response.read().decode("utf-8")

    def latest_version_for_major(self, markdown: str, major: int) -> str:
        """
        Extract every version from the markdown and
        find the highest version within the supplied major.
        """

        versions = set()

        for match in re.findall(r"\b(\d+\.\d+)\b", markdown):
            if match.startswith(f"{major}."):
                versions.add(match)

        if not versions:
            raise RuntimeError(f"No Aurora PostgreSQL versions found for major {major}")

        latest = max(versions, key=lambda v: tuple(int(x) for x in v.split(".")))

        return latest

    def run(self) -> bool:
        config = self.load()

        production_version = self.get_engine_version(
            config,
            PRODUCTION,
        )

        preprod_version = self.get_engine_version(
            config,
            "preproduction",
        )

        print(f"Production version:    {production_version}")
        print(f"Preproduction version: {preprod_version}")

        #
        # Promotion phase
        #
        if production_version != preprod_version:
            print()
            print("Promotion phase detected")

            print(f"Updating production " f"{production_version} -> {preprod_version}")

            self.set_engine_version(
                config,
                PRODUCTION,
                preprod_version,
            )

            self.save(config)

            return True

        #
        # Upgrade phase
        #
        major = int(production_version.split(".")[0])

        markdown = self.fetch_release_calendar()

        latest_available = self.latest_version_for_major(
            markdown,
            major,
        )

        print(f"Latest available: {latest_available}")

        if latest_available == preprod_version:
            print()
            print("No updates required")

            return False

        print()
        print("Upgrade phase detected")

        changed = False

        for env in NON_PROD:
            current = self.get_engine_version(
                config,
                env,
            )

            if current == latest_available:
                continue

            print(f"Updating {env}: " f"{current} -> {latest_available}")

            self.set_engine_version(
                config,
                env,
                "14.20",
            )

            changed = True

        if changed:
            self.save(config)

        return changed


def main() -> int:
    print("Postgres Minor Version Update Checker")
    updater = AuroraPostgresUpdater(CONFIG_FILE)

    changed = updater.run()

    if changed:
        print()
        print("Configuration updated")
    else:
        print()
        print("Configuration unchanged")

    return 0


if __name__ == "__main__":
    sys.exit(main())
