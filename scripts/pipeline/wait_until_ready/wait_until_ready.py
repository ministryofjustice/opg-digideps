import sys
import time
from typing import Any

import boto3
from botocore.exceptions import BotoCoreError, ClientError

POLL_INTERVAL_SECONDS = 15


def print_header(cluster: str, timeout_mins: int, services: list[str]) -> None:
    print("========================================")
    print(f"Cluster:  {cluster}")
    print(f"Services: {' '.join(services)}")
    print(f"Timeout:  {timeout_mins} minutes")
    print("========================================")
    print("")


def get_service_map(
    ecs: Any, cluster: str, services: list[str]
) -> tuple[dict[str, Any], list[dict[str, Any]]]:
    response = ecs.describe_services(cluster=cluster, services=services)
    service_map = {
        service["serviceName"]: service for service in response.get("services", [])
    }
    failures = response.get("failures", [])
    return service_map, failures


def print_recent_events(service: dict[str, Any]) -> None:
    events = service.get("events", [])[:5]
    if not events:
        print("No recent events available")
        return
    print("Last 5 events:")
    for event in events:
        created_at = event.get("createdAt")
        message = event.get("message", "")
        print(f"- {created_at}: {message}")


def evaluate_service(
    service_name: str, service: dict[str, Any] | None
) -> tuple[bool, bool]:
    if service is None:
        print(f"{service_name}: not found yet")
        return False, False

    status = service.get("status", "")
    desired = service.get("desiredCount")
    running = service.get("runningCount")
    deployments = service.get("deployments", [])

    if status != "ACTIVE":
        print(f"{service_name}: status={status}, waiting")
        return False, False

    for deployment in deployments:
        if deployment.get("rolloutState") == "FAILED":
            print(f"{service_name}: deployment entered FAILED state")
            print("")
            print_recent_events(service)
            return False, True

    primary_rollout = ""
    in_progress = False
    for deployment in deployments:
        if deployment.get("status") == "PRIMARY":
            primary_rollout = deployment.get("rolloutState", "")
        if deployment.get("rolloutState") == "IN_PROGRESS":
            in_progress = True

    if desired is None or running is None:
        print(f"{service_name}: missing desired or running count")
        return False, False

    if running != desired or primary_rollout != "COMPLETED" or in_progress:
        rollout = primary_rollout if primary_rollout else "UNKNOWN"
        print(
            f"{service_name}: running={running}/{desired}, primary={rollout}, waiting"
        )
        return False, False

    print(f"{service_name}: running={running}/{desired}, stable")
    return True, False


def main() -> int:
    if len(sys.argv) < 4:
        print(
            "Usage: wait_until_ready.py <cluster> <timeout_mins> <service1> [<service2> ...]"
        )
        return 1

    cluster = sys.argv[1]
    try:
        timeout_mins = int(sys.argv[2])
    except ValueError:
        print("Timeout must be an integer")
        return 1

    services = sys.argv[3:]
    deadline = time.time() + (timeout_mins * 60)

    ecs = boto3.client("ecs")

    print_header(cluster, timeout_mins, services)

    while True:
        if time.time() >= deadline:
            print(
                f"Timed out after {timeout_mins} minutes waiting for services to stabilise."
            )
            return 1

        try:
            service_map, failures = get_service_map(ecs, cluster, services)
        except (ClientError, BotoCoreError) as exc:
            print(f"Failed to query ECS: {exc}")
            time.sleep(POLL_INTERVAL_SECONDS)
            continue

        failed_names = {failure.get("arn", "").split("/")[-1] for failure in failures}
        all_stable = True

        for service_name in services:
            service = service_map.get(service_name)
            if service_name in failed_names and service is None:
                print(f"{service_name}: not found yet")
                all_stable = False
                continue

            stable, hard_fail = evaluate_service(service_name, service)
            if hard_fail:
                return 1
            if not stable:
                all_stable = False

        if all_stable:
            print("")
            print("All services stable")
            return 0

        remaining = max(0, int((deadline - time.time()) // 60))
        print(
            f"--- {remaining} minutes remaining, sleeping {POLL_INTERVAL_SECONDS}s ---"
        )
        print("")
        time.sleep(POLL_INTERVAL_SECONDS)


if __name__ == "__main__":
    raise SystemExit(main())
