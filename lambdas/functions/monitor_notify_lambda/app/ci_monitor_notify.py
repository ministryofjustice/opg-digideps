import argparse
import os
import monitor_notify


def main():
    parser = argparse.ArgumentParser(description="Post-release Slack notifications.")
    parser.add_argument("--success", type=str, default="yes", help="yes or no")
    parser.add_argument(
        "--commit_message",
        type=str,
        default="",
        help="Commit message to include in slack notification",
    )
    parser.add_argument("--branch", type=str, help="Branch we are testing")
    parser.add_argument(
        "--scheduled_task",
        type=str,
        default="",
        help="Name of scheduled task or blank if not scheduled",
    )
    parser.add_argument(
        "--failure_reason",
        type=str,
        default="",
        help="Reason of Failure. Will Update Notifacation.",
    )
    args = parser.parse_args()

    gh_server = str(os.getenv("GITHUB_SERVER_URL", ""))
    gh_repository = str(os.getenv("GITHUB_REPOSITORY", ""))
    gh_run_id = str(os.getenv("GITHUB_RUN_ID", ""))
    github_workflow = str(os.getenv("GITHUB_WORKFLOW", ""))
    actor = str(os.getenv("GITHUB_ACTOR", "actor not included"))

    frontend_url = (
        "https://complete-deputy-report.service.gov.uk"
        if args.branch == "main"
        else f"https://{args.branch}.digideps.opg.service.justice.gov.uk"
    )
    admin_url = (
        "https://admin.digideps.opg.service.justice.gov.uk"
        if args.branch == "main"
        else f"https://{args.branch}.admin.digideps.opg.service.justice.gov.uk"
    )

    data = {
        "GithubActions": {
            "WorkflowName": github_workflow,
            "GhActor": actor,
            "Success": args.success,
            "JobUrl": f"{gh_server}/{gh_repository}/actions/runs/{gh_run_id}",
            "Branch": args.branch,
            "FrontendUrl": frontend_url,
            "AdminUrl": admin_url,
            "CommitMessage": args.commit_message,
            "ScheduledTask": args.scheduled_task,
            "FailureReason": args.failure_reason,
        }
    }

    monitor_notify.lambda_handler(data, "")


if __name__ == "__main__":
    main()
