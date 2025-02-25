# Document and Checklist Sync Process

Documents and checklists need to be synced from DigiDeps to Sirius, where they are stored in the Sirius case management system.

## Overview
This process uses Symfony commands to find unsynced checklists and documents and send them to Sirius. The process looks for items with a **QUEUED** status and attempts to sync them.

- **Checklists** are sent to Sirius immediately.
- **Documents attached to a report** are sent in two stages:
  1. The generated PDF is sent first. This returns a `uuid`, which is used as the `parentUuid` for all subsequent supporting documents.
  2. Supporting documents are then sent, linked to the initial `uuid`.

For this explanation, we will focus on document syncing (checklists follow a similar process).

## Sync Process
The process of syncing documents follows these steps:

1. **Command Execution**
   - In non-production environments, a **CloudWatch event** triggers the command once every 24 hours.
   - In production, the command runs **every minute** via a cron job inside a continuously running container.
   - This setup balances cost and efficiency: keeping a container always up in production avoids unnecessary configuration costs, while in non-prod, we avoid the cost of an always-on container by scheduling executions.

2. **Document Processing**
   - The command searches for documents with the **QUEUED** status.
   - The status is updated to **IN_PROGRESS** while sending to Sirius.
   - The document data is extracted from the database and formatted into a POST request to our **API Gateway Deputy Reporting Integration**.

3. **Integration Handling**
   - The **API Gateway** receives the request and validates the payload.
   - It checks that the request is signed with the correct **IAM role key**.
   - If authentication and validation pass, the request is forwarded to the **Deputy Reporting Lambda**, running in the Sirius-side AWS account.

4. **Lambda Processing**
   - The Lambda function:
     - Authenticates with Sirius using JWT.
     - Retrieves the document from the **DigiDeps S3 bucket**.
     - Formats the data into a request for the **Sirius Public API**.
     - Handles responses from Sirius to process different outcomes.
   - The function then sends a **synchronous response** back to DigiDeps.

5. **Handling Success & Failure**
   - **On success:**
     - The document status updates to **SUCCESS**.
     - If it's a report document, Sirius sends back a `uuid`, which is used to attach supporting documents.
   - **On failure:**
     - The failure count increments, and the process retries on the next run.
     - If the failure persists, the document status updates to **PERMANENT_ERROR**, and error details are logged in the database.

## Infrastructure & Scheduling
- **Non-production:** A **CloudWatch scheduled task** runs once every 24 hours to trigger the sync command.
- **Production:** A permanently running container executes a loop:
  - Sleeps for one minute.
  - Runs the document and checklist sync commands.
  - This method is more cost-effective than triggering a new container every minute via CloudWatch events.

## Testing
- We use a **local container image** that mocks the API Gateway and returns expected responses, simulating Sirius.
- The API Gateway follows an **OpenAPI spec**, as does our mock setup, allowing us to test validation rules.
- The same mock is deployed in AWS for **integration testing**.
- We use **Pact testing** for API versioning, though its necessity may be reviewed in the future due to the stability of the API.

## Monitoring & Alerting
- The **Admin Panel** provides visibility into documents with **QUEUED, IN_PROGRESS, or ERROR** statuses under the **Submissions tab**.
- If a document is archived, it will no longer be visible.
- Admin users can attempt to **resynchronize** documents or recreate reports if necessary.
  - Only recreate a report if the error suggests it was not found (e.g., due to a connectivity issue while generating the report).
- Some errors, such as **Sirius downtime or temporary network issues**, may trigger **automatic overnight retries**.
- Alerts are sent for failures—these should be investigated and resolved as needed.

## Resolving Issues
To diagnose issues:

1. **Check Logs**
   - The most detailed errors appear in **CloudWatch Insights** in the **Sirius AWS account**.
   - Validation errors occur before hitting the Lambda, so API Gateway logs are also helpful.
   - Use log queries around the failure timestamp to pinpoint issues.

2. **Manual Intervention**
   - If an issue cannot be resolved, admin staff can manually transfer documents and archive them in DigiDeps.
   - However, fixing the root cause is preferable.

Most errors are either **temporary** or **data-related** and can be addressed
