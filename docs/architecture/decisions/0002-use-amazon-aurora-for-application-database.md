# 2. Use Amazon Aurora Serverless for ephemeral environments

Date: 2020-02-14

## Status

Accepted

## Context

DigiDeps uses a Postgres database to store persistent information. Since the project was first set up, we have been using an Amazon RDS hosted instance of Postgres for this. However, this hosting option lacks scalability so we have to pay for a full database host 24/7 for each environment.

We have several environments which do not need to be highly available. This includes "ephemeral" development environments, the "main" environment used in CI, and the training environment. We do not need to run a database for these environments outside of working hours, and often inside of them too.

## Decision

We will use Amazon Aurora Serverless for environments which do not need to always be on. Aurora automatically scales with usage, including pausing completely if the database isn't in use.

## Consequences

We will need to stop running regular healthchecks in these environments, since this prevents the database from pausing.

Our database infrastructure will vary between accounts, meaning we cannot be certain that code which worked in development will work in production. Smoke tests in preproduction will indicate any infrastructure failures before release to production.

We will consider upgrading our production database to a matching Postgres version (10.7) and/or hosting it with Provisioned Aurora to further align in the future.

As Aurora identifies itself as Postgres, no application changes are needed to support this.
