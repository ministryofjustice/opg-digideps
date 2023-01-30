### Cancel redundant builds

The purpose of this script is to cancel previous running workflows on the same branch as the current workflow but to
avoid cancelling:

- Terraform running workflows (or whatever other jobs you wish to not auto cancel)
- Production jobs on the 'main' branch

You can control these two parameters through the args `terms_to_waitfor` and `prod_job_terms` respectively.

They check for the terms you enter against the names of the jobs. The `terms_to_waitfor` is any job that you wish to
wait for before cancelling the workflow. Whereas, for any job that matches the  `prod_job_terms`, the entire workflow is
ignored and won't be cancelled.
