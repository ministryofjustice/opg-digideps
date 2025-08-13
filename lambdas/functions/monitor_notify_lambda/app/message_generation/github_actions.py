import logging

logger = logging.getLogger(__name__)
logger.setLevel("INFO")


def github_actions_message(message):
    branch = message["Branch"]
    logger.info(f"Attempting to process github actions event for branch {branch}")

    workflow_name = message["WorkflowName"]
    github_actor = message["GhActor"]
    success = message["Success"]
    job_url = message["JobUrl"]
    frontend_url = message["FrontendUrl"]
    admin_url = message["AdminUrl"]
    commit_message = message["CommitMessage"]
    scheduled_task = message["ScheduledTask"]
    failure_reason = message["FailureReason"]

    path_to_live = True if "Path to live" in workflow_name else False

    status_emoji = ":white_check_mark:" if success == "yes" else ":x:"
    success_string = "Success" if success == "yes" else "Failure"
    workflow_type = "Digideps Live Release" if path_to_live else "Digideps Workflow"
    extra_emoji = ":rocket:" if path_to_live and success == "yes" else ""

    if scheduled_task != "":
        with open("templates/github_actions_scheduled_task.txt", "r") as file:
            template_text = file.read()

        formatted_text = template_text.format(
            scheduled_task=scheduled_task,
            status_emoji=status_emoji,
            success_string=success_string,
            job_url=job_url,
        )
    else:
        with open("templates/github_actions.txt", "r") as file:
            template_text = file.read()

        workflow_name = failure_reason if len(failure_reason) > 0 else workflow_name

        formatted_text = template_text.format(
            workflow_name=workflow_name,
            workflow_type=workflow_type,
            extra_emoji=extra_emoji,
            github_actor=github_actor,
            status_emoji=status_emoji,
            success_string=success_string,
            job_url=job_url,
            branch=branch,
            frontend_url=frontend_url,
            admin_url=admin_url,
            commit_message=commit_message,
        )

    channel = "team" if len(failure_reason) > 0 else "default"

    payload = {"text": formatted_text, "channel": channel}

    return payload
