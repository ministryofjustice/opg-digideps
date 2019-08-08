# Technical debt

A log of technical debt we're aware of and have accepted. These aren't "problems", but we might like to address them in the future if we have suitable time and resource, if related work gives an opportunity, or if they become more pressing.

- There are several files and folders we don't use any more which can be safely removed
- We should use environment variables as feature switches, rather than having separate config files/envtrypoints for each environment
- The environment variable `env` is poorly named. It distinguishes between the public and admin sites, so should be called something like `subsite` for clarity. Similarly, the values (currently "admin" and "front") could be improved to be clearer.
- We don't need to generate `behat.yml` or `parameters.yml` through confd. We should use environment variables to populate them instead, deobfuscating our code.
- We define status label translations in `common.en.yml` (twice), `ndr-overview.en.yml` (twice) and `report-overview.en.yml` (twice). Using one, clear definition would make it easier to make changes in the future
- We should use `bin/phpunit` when running tests (instead of `vendor/phpunit/phpunit/phpunit`)
- `behat-debugger.php` is a poor way of dealing with test failures: it requires explicit setup in our NGINX configuration files and doesn't always collect useful information (e.g. just showing "Application error" when something crashes)
- We have several duplicated template files (albeit the main content differs) that we could consider making reusable. `start.html.twig` and `add_another.html.twig` are good examples to start with
- Since version 5.2 of `sensio/framework-extra-bundle`, the `Route` and `Method` annotations have been deprecated and moved into the Symfony routing component. We should deprecate the bundle from using it with:
```$xslt
sensio_framework_extra:
    router:
        annotations: false
```
and replace the annotations `Sensio\Bundle\FrameworkExtraBundle\Configuration\Route` and `Sensio\Bundle\FrameworkExtraBundle\Configuration\Method` with `Symfony\Component\Routing\Annotation\Route`
- We should move Composer and NPM into separate containers so we can mount the vendor/assets folders first then build onto both the host and container (see DDPB-2732)

