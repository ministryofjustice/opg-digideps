# Pull Request Procedure

Our setup for creating a pull request and merging code into our main branch is fairly standard and works as follows:

- Create a branch and name it after the your ticket number from jira. It's best to not add any description to
this as it is used in the naming of terraform resources. Example: `DDPB-1234`.

- Spin up your local environment as specified in the readme file at the root of this repo.

- Make the code changes required by your story in Jira and check they are working in your local environment. For more
complicated tickets, it's worth running the test suites locally to check everything works with your changes.

- Use git to add your files (`git add <files*>`) and make a commit that is concise and in the imperative format and
starts with the branch name. Example `git commit -m 'DDPB-1234 add scheduling of document task feature'`

- When you are ready, you can make a pull request. No one will approve this yet. It is simply to kick off the workflow
that will build your environment in AWS and run all unit and integration tests.

- Because of duration of our behat tests currently we have an approval step to move on to further testing (to save on circleci credits).
We aim to remove this once we have improved behat test duration.

- If all the tests pass and it is ready for approval then fill in the pull request template giving all relevant details.
If not then you can continue submitting commits to the branch which will kick off the rebuild process.

- Put a link to your jira ticket in the Dev channel `opg-digideps-devs`. This should have automatically linked
through to your ticket so your colleagues can go and review your code.

- Fix any comments that are requested and when you have been given approval to merge then you should move the ticket
across the board on jira to acceptance where it can be signed off by a product manager. If the ticket can't be looked
at by a project manager (internal infrastructure change for example) then proceed to next step.

- Move your ticket across the board to `ready to merge` in jira.

- Your environment will be destroyed asynchronously, not on the current night but the subsequent night (via the circle workflow).

- To make sure we aren't protecting needlessly there's an approval for un-protecting the branch that you
need to click on just before merge. It only takes a few seconds to run. Optionally you can destroy the
environment now from a separate approval. This is useful in case of a bad build to fully reset your env. You
don't need to click this to merge your pull request though.

- Once this process has finished then you are able to merge your PR. You will perform a 'squash and merge' on your PR
and at this point you should tidy up your PR to adhere to the following guidelines:

```
- Prepend the commit message with the branch name
- Separate subject from body with a blank line
- Limit the subject line to 50 characters
- Capitalize the subject line
- Do not end the subject line with a period
- Use the imperative mood in the subject line
- Wrap the body at 72 characters
- Use the body to explain what and why vs. how
```

- Move your ticket to `release` on the jira board.
