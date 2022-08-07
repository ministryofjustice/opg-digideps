# 6. JWT usage

Date: 2022-07-15

## Status

Accepted

## Context

Digideps currently relies on a symmetric secret string that acts as authorisation for unsecured endpoints in the client and API apps. While this has been working for us so far it's not a secure way of locking down our public endpoints as if the secret string is ever made public the endpoints can be accessed by anyone.

We need a battle tested way of securing public endpoints that gives us the flexibility to expand to more than two services in the future and more granular control over who/what gets access to specific endpoints.

## Decision

We have implemented JWT based authentication in order to secure endpoints in the application. Currently, this is a single endpoint in our stats section and locked down to super admin users. Going forward we can roll this out to all user types, and we have a mechanism to authenticate other services to integrate with Digideps.

There remains an open question on if JWTs should replace our session based authentication that's baked in to Symfony but until we have upgraded to at least Symfony 5.4 and fully implemented the required security system changes it's not something we should attempt. Additionally, we are currently storing the JWT in session which is generally not advised. We should move to using a secure cookie to store the JWT when rolling the feature out to all user types.

## Consequences

- Super admin users now require a valid JWT when accessing `stats/deputies/lay/active`
- JWTs are stored in session but should be moved to a secure cookie
- External services now have a route to authenticate with Digideps
