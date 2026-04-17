# API Conventions

We use agreed API conventions to ensure that our APIs are consistent and robust. Consistent APIs are also easier for developers to work with and make upgrades easier.

## Authentication

Authenticate is done through the `/auth/login` endpoint. You will need to set the client token header and provide user credentials, and will be given an AuthToken in the response header.

You will need to use the AuthToken in subsequent requests to authenticate the user.

## API return codes

We use standard HTTP status codes to help client applications understand responses.

- 200 Success
- 403 Missing client secret, or invalid permissions (configuration error) or invalid ACL permissions for logged user
- 404 Not found
- 419 AuthToken missing, expired or not matching (runtime error)
- 421 User registration: User and client not found in casrec
- 422 User registration: email already existing
- 423 Too many login attempts, Locked
- 424 User registration: User and client found, but postcode mismatch
- 425 User registration: Case number already used
- 498 wrong credentials at login
- 499 wrong credentials at login (after many failed requests)
- 500 generic error due to internal exception (e.g. db offline)

## Endpoint conventions

We accept standard HTTP methods (`GET`, `POST`, `PUT`, `DELETE`) for API endpoints. Entities have a single-field ID and that should be provided in the URL whenever an entity is being addressed.

## JMS groups

We use JMS Serializer's [Groups functionality][jms-groups] to group entity properties. When querying, we can specify a group to ensure that the smallest set of data possible is retrieved.

[jms-groups]: https://jmsyst.com/libs/serializer/master/cookbook/exclusion_strategies#creating-different-views-of-your-objects
