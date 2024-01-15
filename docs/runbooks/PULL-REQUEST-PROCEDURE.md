# Pull Request Procedure

Our setup for creating a pull request and merging code into our main branch is fairly standard and works as follows:

- Create a branch and name it after the your ticket number from jira. It's best to not add any description to
this as it is used in the naming of terraform resources. Example: `DDLS-1234`.

- Spin up your local environment as specified in the readme file at the root of this repo.

- Make the code changes required by your story in Jira and check they are working in your local environment. For more
complicated tickets, it's worth running the test suites locally to check everything works with your changes.

- Make your functions small and testable and create unit tests!

- Use git to add your files (`git add <files*>`) and make a commit that is concise and in the imperative format and
starts with the branch name. Example `git commit -m 'DDLS-1234 add scheduling of document task feature'`

- Push your changes up to a branch in github: `git push origin DDLS-1234`

- Make a draft pull request. No one will approve this yet. It is simply to kick off the workflow
that will build your environment in AWS and run all unit and integration tests.

- If all the tests pass and it is ready for approval then fill in the pull request template giving all relevant details.
If not then you can continue submitting commits to the branch which will kick off the rebuild process.

- Convert from draft to a real pull request.

- Your ticket should appear in `opg-digideps-devs`. Make a comment under your ticket in the channel to @digidepsdevs group
to have your colleagues review your code.

- Fix any comments that are requested and when you have been given approval to merge then you should notify a product owner
to review the environment if it is needed. You will need to provide them a link to your env which
can be found in the `opg-digideps-builds` channel.

- If the ticket can't be looked at by a product owner or does not need to be looked at
(internal infrastructure change for example) then proceed to next step.

- Merge your PR using 'squash and merge' and move your ticket in Jira to Done/Completed. The following rules should
be followed for your squashed commit message:
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

- Your environment will now be destroyed immediately. Even when you don't merge, your environment will be destroyed
asynchronously on the current night.

- Avoid merging and leaving for the day. As long as you merge earlier in the day, you shouldn't need to follow it
through to live but if you are merging later in the day, check it has made it out to live and no alerts have been
triggered before leaving. In the unusual event that there is an issue, there will be alerts in our slack channels.
