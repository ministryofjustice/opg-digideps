import sys
import time
import boto3
from botocore.exceptions import ClientError, BotoCoreError

POLL_INTERVAL_SECONDS = 15


def get_service_data(ecs, cluster, services):
    try:
        response = ecs.describe_services(cluster=cluster, services=services)
    except (ClientError, BotoCoreError) as exc:
        print(f"Failed to query ECS: {exc}")
        return None

    service_map = {}
    for service in response.get("services", []):
        service_map[service["serviceName"]] = service

    failed_names = set()
    for failure in response.get("failures", []):
        arn = failure.get("arn", "")
        failed_names.add(arn.split("/")[-1])

    return {
        "service_map": service_map,
        "failed_names": failed_names,
    }


def print_recent_events(service):
    print("Last 5 events:")
    events = service.get("events", [])[:5]

    if not events:
        print("No recent events available")
        return

    for event in events:
        print(f"- {event.get('createdAt')}: {event.get('message')}")


def check_service(service_name, service, failed_names):
    if service_name in failed_names and service is None:
        print(f"{service_name}: not found yet")
        return False

    if service is None:
        print(f"{service_name}: missing service data")
        return False

    status = service.get("status")
    desired = service.get("desiredCount")
    running = service.get("runningCount")
    deployments = service.get("deployments", [])

    if status != "ACTIVE":
        print(f"{service_name}: status={status}, waiting")
        return False

    primary_rollout = ""
    in_progress = False

    for deployment in deployments:
        rollout = deployment.get("rolloutState")

        if rollout == "FAILED":
            print(f"{service_name}: deployment entered FAILED state")
            print("")
            print_recent_events(service)
            sys.exit(1)

        if deployment.get("status") == "PRIMARY":
            primary_rollout = rollout

        if rollout == "IN_PROGRESS":
            in_progress = True

    if desired is None or running is None:
        print(f"{service_name}: missing desired or running count")
        return False

    if running != desired or primary_rollout != "COMPLETED" or in_progress:
        rollout = primary_rollout if primary_rollout else "UNKNOWN"
        print(
            f"{service_name}: running={running}/{desired}, primary={rollout}, waiting"
        )
        return False

    print(f"{service_name}: running={running}/{desired}, stable")
    return True


def wait_for_services(ecs, cluster, timeout_mins, services):
    deadline = time.time() + (timeout_mins * 60)

    while True:
        if time.time() >= deadline:
            print(
                f"Timed out after {timeout_mins} minutes waiting for services to stabilise."
            )
            sys.exit(1)

        data = get_service_data(ecs, cluster, services)

        if data is None:
            time.sleep(POLL_INTERVAL_SECONDS)
            continue

        service_map = data["service_map"]
        failed_names = data["failed_names"]
        all_stable = True

        for service_name in services:
            service = service_map.get(service_name)
            stable = check_service(service_name, service, failed_names)

            if not stable:
                all_stable = False

        if all_stable:
            print("")
            print("All services stable")
            sys.exit(0)

        remaining = max(0, int((deadline - time.time()) // 60))
        print(
            f"--- {remaining} minutes remaining, sleeping {POLL_INTERVAL_SECONDS}s ---"
        )
        print("")
        time.sleep(POLL_INTERVAL_SECONDS)


cluster = sys.argv[1]
timeout_mins = int(sys.argv[2])
services = sys.argv[3:]

ecs = boto3.client("ecs")

print("========================================")
print(f"Cluster:  {cluster}")
print(f"Services: {' '.join(services)}")
print(f"Timeout:  {timeout_mins} minutes")
print("========================================")
print("")

wait_for_services(ecs, cluster, timeout_mins, services)
