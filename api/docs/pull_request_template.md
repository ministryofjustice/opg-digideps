## Purpose
_Briefly describe the purpose of the change, and/or link to the JIRA ticket for context_

Fixes [DDPB-####](https://opgtransform.atlassian.net/browse/DDPB-####)

## Approach
_Explain how your code addresses the purpose of the change_

## Learning
_Any tips and tricks, blog posts or tools which helped you. Plus anything notable you've discovered about DigiDeps_

## Checklist
- [ ] I have performed a self-review of my own code
- [ ] I have updated documentation (Confluence/GitHub wiki/tech debt doc) where relevant
- [ ] I have added tests to prove my work, and they follow our [best practices](https://github.com/ministryofjustice/opg-digi-deps-client/wiki/Testing-best-practices)
- [ ] I have successfully built my branch to a feature environment
- [ ] New and existing unit tests pass locally with my changes (`docker-compose run --rm api sh scripts/apiunittest.sh`)
- [ ] There are no Composer security issues (`docker-compose run api php app/console security:check`)
- [ ] The product team have tested these changes
