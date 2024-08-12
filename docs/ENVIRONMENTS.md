## Digideps - Environments

In digideps, we have a number of environments that are used for different purposes.

#### Production
The live environment that end users access

#### Preproduction
An environment configured as live that we can use for diagnosing complex issues and testing upgrades.

#### Training
Internal training environment used for staff to both do their training and troubleshoot basic user issues.

#### Integration
Used for both our end to end tests in the path to live pipeline and for various other automated tests and integrations with other systems.

#### Development
Can be manually spun up via github dispatch workflow. Publicly accessible with fixture data. Used for showing external stakeholders our application.

#### Branch (ephemereal environments)
On any PR, a temporary environment will be spun up that is accessible to OPG staff. All pull request github action jobs will be run against this environment.
