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
- [ ] New and existing unit tests pass locally with my changes (`docker-compose run --rm test sh scripts/clienttest.sh`)
- [ ] The product team have tested these changes

### API
- [ ] There are no Composer security issues (`docker-compose run api php app/console security:check`)

### Frontend
- [ ] There are no new frontend linting errors (`docker-compose run --rm npm run lint`)
- [ ] There are no NPM security issues (`docker-compose run --rm npm audit`)
- [ ] There are no Composer security issues (`docker-compose run frontend php app/console security:check`)
- [ ] I have run an in-browser accessibility test (e.g. WAVE, Lighthouse)
- [ ] There are no deprecated CSS classes noted in the profiler
- [ ] Translations are used and the profiler doesn't identify any missing
