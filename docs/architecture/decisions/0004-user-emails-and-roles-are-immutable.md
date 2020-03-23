# 4. User emails and roles are immutable

Date: 2020-03-23

## Status

Accepted

## Context

Due to the freedom given to users and admins in changing a user's email address or role, we've had issues identifying why people have certain permissions in DigiDeps. As well as being confusing, we believe this has led to security issues where a high-authority user has had their email changed in an attempt to reuse the account, thereby granting unreasonable access to the owner of the new email address.

There are rarely good reasons to change a user's email address or role. Because very little in DigiDeps belongs directly to a user, these situtations can be resolved by deleting the original account and creating a new one with the correct permissions.

## Decision

User email addresses and roles will henceforth be immutable: they are set when a new user is created and cannot subsequently be changed.

There is one exception to this: organisation administrators will be able to switch users between team member and admin roles. This role switch is entirely internal to the organisation and doesn't affect any view/edit permissions to clients and reports.

## Consequences

This decision will prevent user accounts' permissions from being changed, ensuring they cannot access clients they shouldn't. It will also ensure that we can identify an account and trust its ownership/permissions have not been different throughout its history.

This decision will make the process of deputies changing email address more difficult, since the address can no longer be edited in-place. However, organisations can still self-serve to invite the new email address (and delete the old one), and lay deputies can ask the helpdesk to delete their old account so they can register the new one.
