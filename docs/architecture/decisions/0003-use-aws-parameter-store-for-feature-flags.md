# 3. Use AWS Parameter Store for feature flags

Date: 2020-03-12

## Status

Accepted

## Context

We want to use feature flags in the service to allow us to easily enable and disable functionality. Feature flags should be easy to change, audited and able to take effect immediately.

## Decision

We will use AWS Parameter Store to store our feature flags in a standardised format: `/{environment}/flag/{flagName}`. This will make flags easy to identify, change and debug. Operators will have access to change the value of these flags.

Resources inside our service can access feature flags either by having them passed in as environment variables, or by directly querying Parameter Store on-demand.

Parameter Store values must be strings, so we will consistently use the values `0` (off) and `1` (on).

## Consequences

This provides a low-cost, partially-managed service with access control and auditing built in. However, it adds a dependency on an AWS service that we should sensibly abstract to reduce tight coupling.
