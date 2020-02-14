# 2. Use Amazon Aurora for application database

Date: 2020-02-14

## Status

Accepted

## Context

DigiDeps uses a Postgres database to store persistent information. Since the project was first set up, we have been using an Amazon RDS hosted instance of Postgres for this. However, this hosting option lacks scalability so we have to pay for a full database host 24/7 for each environment.

Further, hosted Postgres is hard to upgrade and make resilient.

## Decision

We will use Amazon Aurora for Postgres. Aurora provides more scalability and flexibility, automatically applies minor upgrades and provides replica functionality to provide instant failover in the event of an outage.

In production we will use provisioned Aurora with a failover replica. This will ensure our database is always awake and has capacity. We will use the same in preproduction, to provide a comparable environment for testing.

In all other environments, we will use serverless Aurora. This automatically scales with usage, including pausing completely if the database isn't in use. This will save on costs as most of our environments aren't in use outside of working hours (some environments can be unused for days).

## Consequences

We will need to stop running regular healthchecks on our non-production servers, since this prevents the database from pausing.

As Aurora identifies itself as Postgres, no application changes will be needed.
