# Technical debt

A log of technical debt we're aware of and have accepted. These aren't "problems", but we might like to address them in the future if we have suitable time and resource, if related work gives an opportunity, or if they become more pressing.

- There are several files and folders we don't use any more which can be safely removed
- We should use environment variables as feature switches, rather than having separate config files/envtrypoints for each environment
- We should use `bin/phpunit` when running tests (instead of `vendor/phpunit/phpunit/phpunit`)
