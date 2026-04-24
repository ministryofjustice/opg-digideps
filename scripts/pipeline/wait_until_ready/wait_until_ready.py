import sys
import time
import boto3
from botocore.exceptions import ClientError, BotoCoreError

POLL_INTERVAL_SECONDS = 5


def log(message=""):
    print(message, flush=True)


def get_service_data(ecs, cluster, services):
    try:
        response = ecs.describe_services(cluster=cluster, services=services)
    except (ClientError, BotoCoreError) as exc:
        log(f"Failed to query ECS: {exc}")
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
        log(f"{name}: ECS describe failure: {reason}")

    return {
        "service_map": service_map,
        "failed_names": failed_names,
    }


def print_recent_events(service):
    log("Last 5 events:")
    events = service.get("events", [])[:5]

    if not events:
        log("No recent events available")
        return

    for event in events:
        log(f"- {event.get('createdAt')}: {event.get('message')}")


def get_primary_deployment(deployments):
    for deployment in deployments:
        if deployment.get("status") == "PRIMARY":
            return deployment
    return None


def check_service(service_name, service, failed_names):
    if service_name in failed_names and service is None:
        log(f"{service_name}: not found yet")
        return False

    if service is None:
        log(f"{service_name}: missing service data")
        return False

    status = service.get("status")
    desired = service.get("desiredCount")
    running = service.get("runningCount")
    deployments = service.get("deployments", [])

    if status != "ACTIVE":
        log(f"{service_name}: status={status}, waiting")
        return False

    for deployment in deployments:
        if deployment.get("rolloutState") == "FAILED":
            log(f"{service_name}: deployment entered FAILED state")
            log("")
            print_recent_events(service)
            sys.exit(1)

    if desired is None or running is None:
        log(f"{service_name}: missing desired or running count")
        return False

    if desired == 0:
        log(f"{service_name}: desired=0, ready")
        return True

    primary = get_primary_deployment(deployments)

    if primary is None:
        log(f"{service_name}: no primary deployment found, waiting")
        return False

    primary_running = primary.get("runningCount", 0)
    primary_desired = primary.get("desiredCount", 0)
    primary_rollout = primary.get("rolloutState", "UNKNOWN")
    task_definition = primary.get("taskDefinition", "").split("/")[-1]

    if primary_running >= 1:
        log(
            f"{service_name}: ready, primary_running={primary_running}/{primary_desired}, "
            f"service_running={running}/{desired}, rollout={primary_rollout}, task={task_definition}"
        )
        return True

    log(
        f"{service_name}: waiting, primary_running={primary_running}/{primary_desired}, "
        f"service_running={running}/{desired}, rollout={primary_rollout}, task={task_definition}"
    )
    return False


def wait_for_services(ecs, cluster, timeout_mins, services):
    deadline = time.time() + (timeout_mins * 60)

    while True:
        if time.time() >= deadline:
            log(
                f"Timed out after {timeout_mins} minutes waiting for services to become ready."
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
            log("")
            log("All services ready")
            sys.exit(0)

        remaining = max(0, int((deadline - time.time()) // 60))
        log(f"--- {remaining} minutes remaining, sleeping {POLL_INTERVAL_SECONDS}s ---")
        log("")
        time.sleep(POLL_INTERVAL_SECONDS)


cluster = sys.argv[1]
timeout_mins = int(sys.argv[2])
services = sys.argv[3:]

ecs = boto3.client("ecs")

log("========================================")
log(f"Cluster:  {cluster}")
log(f"Services: {' '.join(services)}")
log(f"Timeout:  {timeout_mins} minutes")
log("========================================")
log("")

wait_for_services(ecs, cluster, timeout_mins, services)
