# Tech Debt

This file should be used to record known debt.

If any debt may lead to larger risks, it should be transferred to the risk board. If we have identified an agreed solution to pay down the debt, it should be moved to a ticket in JIRA.

## CI
- Notify channel when environment ready

## Infra
- scheduled scaling for cost saving
- periodically rotate CI access key
- move public routes to main route table
- Move to secrets per account
- Nginx timeout is universally high to allow for some use cases (e.g. compiling zips)
- Security group naming and descriptions are inconsistent - they should be standardised
- Restrict inbound and outbound rules - certain undesirable flows are permissible

## Application
- Separate config files/entrypoints are inflexible and obfuscating, should use feature flags instead
- `web/assets` being generated in frontend image means it can't be mounted for live-editing
- Change Client Users relationship from array to ArrayCollection ?
- Remove team code from application once organisation has replaced and stabalised.
- Revise ReportService unit tests. Too much setup making it difficult to manipulate mock objects in tests.
   testSubmitValidNdr() test needs different CASREC properties to properly test correct report type is generated from NDR

## Design
- Status label texts are variously defined in `common.en.yml`, `ndr-overview.en.yml` and `report-overview.en.yml`
- Many templates are duplicated (e.g. start, yes/no questions, add another)
