import sys
import time
import boto3
from botocore.exceptions import ClientError, BotoCoreError

POLL_INTERVAL_SECONDS = 5


def get_service_data(ecs, cluster, services):
    try:
        response = ecs.describe_services(cluster=cluster, services=services)
    except (ClientError, BotoCoreError) as exc:
        print(f"Failed to query ECS: {exc}", flush=True)
        return None

    service_map = {}
    for service in response.get("services", []):
        service_map[service["serviceName"]] = service

    failed_names = set()
    for failure in response.get("failures", []):
        arn = failure.get("arn", "")
        reason = failure.get("reason", "UNKNOWN")
        name = arn.split("/")[-1]
        failed_names.add(name)
        print(f"{name}: ECS describe failure: {reason}", flush=True)

    return {
        "service_map": service_map,
        "failed_names": failed_names,
    }


def print_recent_events(service):
    print("Last 5 events:", flush=True)

    events = service.get("events", [])[:5]

    if not events:
        print("No recent events available", flush=True)
        return

    for event in events:
        print(f"- {event.get('createdAt')}: {event.get('message')}", flush=True)


def check_service(service_name, service, failed_names):
    if service_name in failed_names and service is None:
        print(f"{service_name}: not found yet", flush=True)
        return False

    if service is None:
        print(f"{service_name}: missing service data", flush=True)
        return False

    status = service.get("status")
    desired = service.get("desiredCount", 0)
    deployments = service.get("deployments", [])

    if status != "ACTIVE":
        print(f"{service_name}: status={status}, waiting", flush=True)
        return False

    for deployment in deployments:
        if deployment.get("rolloutState") == "FAILED":
            print(f"{service_name}: deployment entered FAILED state", flush=True)
            print("", flush=True)
            print_recent_events(service)
            sys.exit(1)

    primary = None

    for deployment in deployments:
        if deployment.get("status") == "PRIMARY":
            primary = deployment
            break

    if primary is None:
        print(f"{service_name}: no primary deployment yet", flush=True)
        return False

    primary_running = primary.get("runningCount", 0)
    primary_desired = primary.get("desiredCount", 0)
    rollout = primary.get("rolloutState", "UNKNOWN")
    task_definition = primary.get("taskDefinition", "").split("/")[-1]

    if desired == 0:
        if rollout == "COMPLETED":
            print(
                f"{service_name}: ready (scaled to zero, rollout completed)",
                flush=True,
            )
            return True

        print(
            f"{service_name}: waiting (scaled to zero, rollout={rollout}, task={task_definition})",
            flush=True,
        )
        return False

    if primary_running >= 1:
        print(
            f"{service_name}: ready "
            f"(primary_running={primary_running}/{primary_desired}, rollout={rollout}, task={task_definition})",
            flush=True,
        )
        return True

    print(
        f"{service_name}: waiting "
        f"(primary_running={primary_running}/{primary_desired}, rollout={rollout}, task={task_definition})",
        flush=True,
    )
    return False


def wait_for_services(ecs, cluster, timeout_mins, services):
    deadline = time.time() + (timeout_mins * 60)

    while True:
        if time.time() >= deadline:
            print(
                f"Timed out after {timeout_mins} minutes waiting for services to become ready.",
                flush=True,
            )
            sys.exit(1)

        data = get_service_data(ecs, cluster, services)

        if data is None:
            time.sleep(POLL_INTERVAL_SECONDS)
            continue

        service_map = data["service_map"]
        failed_names = data["failed_names"]
        all_ready = True

        for service_name in services:
            service = service_map.get(service_name)
            ready = check_service(service_name, service, failed_names)

            if not ready:
                all_ready = False

        if all_ready:
            print("", flush=True)
            print("All services ready", flush=True)
            sys.exit(0)

        remaining = max(0, int((deadline - time.time()) // 60))
        print(
            f"--- {remaining} minutes remaining, sleeping {POLL_INTERVAL_SECONDS}s ---",
            flush=True,
        )
        print("", flush=True)

        time.sleep(POLL_INTERVAL_SECONDS)


if len(sys.argv) < 4:
    print(
        "Usage: wait_until_ready.py <cluster> <timeout_minutes> <services...>",
        flush=True,
    )
    sys.exit(1)


cluster = sys.argv[1]
timeout_mins = int(sys.argv[2])
services = sys.argv[3:]

ecs = boto3.client("ecs")

print("========================================", flush=True)
print(f"Cluster:  {cluster}", flush=True)
print(f"Services: {' '.join(services)}", flush=True)
print(f"Timeout:  {timeout_mins} minutes", flush=True)
print("========================================", flush=True)
print("", flush=True)

wait_for_services(ecs, cluster, timeout_mins, services)
