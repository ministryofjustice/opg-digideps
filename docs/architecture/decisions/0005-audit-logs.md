# 5. Audit logs

Date: 2022-05-13

## Status

Accepted

## Context

Within DigiDeps we have a number of actions that can destory connections between pieces of data in a permanent way. For example when a new CSV export from the system of record is performed and imported. Or a deputy is moved between organisations.

We need a method to allow us to track these changes and to allow us to check any claims made by users. Who acted, what the outcome was and when it happened.

## Decision

We have implemented a series of audit logs that are stored within our normal cloudwatch logging system. These contain a changeset which is the output of the changes made, along with the user who did them and a timestamp.

However unlike normal logs these may contain personally identifiable data, so they are treated with an extra level of care from a security point of view. Only users belonging to an explicit group with the right to view PII can access them. This is currently breakglass but this will change to a specific group in the future. As with all  our logs they are encrypted at rest. These logs use the ```audit-``` prefix.

## Consequences

- We will need to provide tooling to interact with access these logs locally in development
- We will need to maintain roles and assignment of users to roles for access management
- Code will be part of the ```App\Service\Audit``` namespace
- log expiry times will need to be agreed with the business