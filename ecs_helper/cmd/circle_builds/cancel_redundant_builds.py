import requests
import os
import json
from time import sleep
import argparse


class CancelPreviousWorkflows:
  def __init__(
    self,
    circle_project_username,
    circle_project_reponame,
    circle_branch,
    circle_builds_token,
    terms_to_waitfor,
    prod_job_terms
  ):

    self.circle_project_username = circle_project_username
    self.circle_project_reponame = circle_project_reponame
    self.circle_branch = circle_branch
    self.circle_builds_token = circle_builds_token
    self.terms_to_waitfor = terms_to_waitfor.split(",")
    self.prod_job_terms = prod_job_terms.split(",")
    self.delay = 10
    if "CIRCLE_WORKFLOW_ID" in os.environ:
      self.current_workflow_id = os.environ["CIRCLE_WORKFLOW_ID"]
    else:
      self.current_workflow_id = "None"

    for term in self.terms_to_waitfor:
      print(f"Term to wait for: {term}")
    for term in self.prod_job_terms:
      print(f"Term to ignore: {term}")

  def get_running_jobs(self):
    running_jobs_url = f"https://circleci.com/api/v1.1/project/github/{self.circle_project_username}/{self.circle_project_reponame}/tree/{self.circle_branch}?circle-token={self.circle_builds_token}"
    response = requests.get(running_jobs_url)
    running_jobs = []
    if response.status_code == 200:
      running_jobs_json = json.loads(response.text)
      for job in running_jobs_json:
        if job['status'] == "queued" or job['status'] == "running":
          if job['workflows']['workflow_id'] != self.current_workflow_id:
            if any(prod_jobs_term not in job['workflows']['job_name'] for prod_jobs_term in self.prod_job_terms):
              running_jobs.append(job['workflows'])
              print(f"Other Job: \"{job['workflows']['job_name']}\", Status: \"{job['status']}\"")
      return running_jobs
    else:
      print(f"API call to circle failed with status code: {response.status_code}")
      return running_jobs

  def tf_job_running(self, running_jobs):
    if len(running_jobs) > 0:
      for job in running_jobs:
        if any(term_to_ignore in job['job_name'] for term_to_ignore in self.terms_to_waitfor):
          print(f"Found terraform job \"{job['job_name']}\"")
          return True
      print(f"Found non terraform job \"{job['job_name']}\"")
      return False
    else:
      print("Found no jobs running")
      return False

  def cancel_workflows(self):
    workflow_ids = []
    running_jobs = self.get_running_jobs()
    for job in running_jobs:
      print(f"Will attempt to cancel workflow: {job['workflow_id']}")
      workflow_ids.append(str(job['workflow_id']))

    unique_workflow_ids = list(set(workflow_ids))

    for workflow_id in unique_workflow_ids:
      print(f"Cancelling workflow: {workflow_id}")
      headers = {"Accept": "application/json"}
      url = f"https://circleci.com/api/v2/workflow/{workflow_id}/cancel?circle-token={self.circle_builds_token}"

      response = requests.post(url, None, headers=headers)
      if response.text == "{\"message\":\"Accepted.\"}":
        print(f"Successfully cancelled workflow: {workflow_id}")
      else:
        print(f"Failed to cancel workflow: {workflow_id}")

  def wait_for_terraform_jobs(self):
    tf_job_exists = False
    tf_running = self.tf_job_running(self.get_running_jobs())
    count = 0

    while tf_running:
      tf_job_exists = True
      sleep(self.delay)
      count = count + 1
      tf_running_jobs = self.get_running_jobs()
      tf_running = self.tf_job_running(tf_running_jobs)
      print(f"Waiting for terraform job \"{tf_running_jobs[0]['job_name']}\" to finish. Waiting {count * self.delay} seconds")

    if tf_job_exists:
      running_jobs = self.get_running_jobs()
      count = 0
      while len(running_jobs) < 1 and count < 12:
        count = count + 1
        print(
          f"Terraform job finished. Waiting for next job to start so we can cancel it. Waiting {count * self.delay} seconds")
        sleep(self.delay)
        success, running_jobs = self.get_running_jobs()
      if len(running_jobs) < 1:
        print("No further jobs running or left to run (or they are taking too long to start")
      else:
        print("Terraform job finished, new job started. Checking if it's another terraform job")
        if self.tf_job_running(self.get_running_jobs()):
          self.wait_for_terraform_jobs()
        else:
          print("New job(s) aren't terraform jobs. Returning jobs to cancel")

    return self.get_running_jobs()

  @staticmethod
  def exiting():
    print("No workflows to cancel from Circle API. Continuing build")
    exit(0)


def main():
  parser = argparse.ArgumentParser(description="Cancel all previous workflows with ignore list.")

  parser.add_argument(
    "--circle_project_username",
    default="ministryofjustice",
    help="Circle username for the workflow.",
  )
  parser.add_argument(
    "--circle_project_reponame",
    default="myproject",
    help="The project for the workflow.",
  )
  parser.add_argument(
    "--circle_branch",
    default="None",
    help="Name of the branch to check for the workflow.",
  )
  parser.add_argument(
    "--circle_builds_token",
    default="notarealtoken",
    help="Personal API token for circle.",
  )
  parser.add_argument(
    "--terms_to_waitfor",
    default="term one, term two",
    help="Strings representing job names separated by commas to 'wait for'.",
  )
  parser.add_argument(
    "--prod_job_terms",
    default="' production', 'shared-production'",
    help="Production job names that you do not want to cancel.",
  )

  args = parser.parse_args()

  cancel_workflows = CancelPreviousWorkflows(
    args.circle_project_username,
    args.circle_project_reponame,
    args.circle_branch,
    args.circle_builds_token,
    args.terms_to_waitfor,
    args.prod_job_terms
  )

  running_jobs = cancel_workflows.get_running_jobs()

  if cancel_workflows.tf_job_running(running_jobs):
    running_jobs = cancel_workflows.wait_for_terraform_jobs()
  if len(running_jobs) > 0:
    cancel_workflows.cancel_workflows()
  else:
    cancel_workflows.exiting()


if __name__ == "__main__":
  main()
