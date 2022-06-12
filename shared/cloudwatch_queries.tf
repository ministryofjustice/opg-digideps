resource "aws_cloudwatch_query_definition" "non_healthcheck_requests" {
  name = "no_healthchecks_or_scanning_output"

  query_string = <<QUERY
fields @timestamp, status, service_name, request_uri, @message
| sort @timestamp desc
| filter ispresent(service_name)
| filter request_uri NOT LIKE /\/manage\/availability|\/manage\/elb/
QUERY
}

resource "aws_cloudwatch_query_definition" "exceptions_and_errors" {
  name = "messages_that_contain_exception_or_error"

  query_string = <<QUERY
fields @timestamp, status, service_name, request_uri, @message
| sort @timestamp desc
| filter @message like /Exception|exception|Error|error/
QUERY
}

resource "aws_cloudwatch_query_definition" "checklist_sync_logs" {
  name = "logs_from_checklist_sync_scheduled_jobs"

  query_string = <<QUERY
fields @timestamp, @message
| sort @timestamp desc
| filter @logStream like 'checklist-sync'
QUERY
}

resource "aws_cloudwatch_query_definition" "document_sync_logs" {
  name = "logs_from_document_sync_scheduled_jobs"

  query_string = <<QUERY
fields @timestamp, @message
| sort @timestamp desc
| filter @logStream like 'document-sync'
QUERY
}
