def check_service(service_name, service, failed_names):
    if service_name in failed_names and service is None:
        log(f"{service_name}: not found yet")
        return False

    if service is None:
        log(f"{service_name}: missing service data")
        return False

    status = service.get("status")
    desired = service.get("desiredCount", 0)
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

    primary = None

    for deployment in deployments:
        if deployment.get("status") == "PRIMARY":
            primary = deployment
            break

    if primary is None:
        log(f"{service_name}: no primary deployment yet")
        return False

    primary_running = primary.get("runningCount", 0)
    primary_desired = primary.get("desiredCount", 0)
    rollout = primary.get("rolloutState", "UNKNOWN")
    task_definition = primary.get("taskDefinition", "").split("/")[-1]

    if desired == 0:
        if rollout == "COMPLETED":
            log(f"{service_name}: ready (scaled to zero, rollout completed)")
            return True

        log(
            f"{service_name}: waiting (scaled to zero, rollout={rollout}, task={task_definition})"
        )
        return False

    if primary_running >= 1:
        log(
            f"{service_name}: ready "
            f"(primary_running={primary_running}/{primary_desired}, rollout={rollout}, task={task_definition})"
        )
        return True

    log(
        f"{service_name}: waiting "
        f"(primary_running={primary_running}/{primary_desired}, rollout={rollout}, task={task_definition})"
    )
    return False
