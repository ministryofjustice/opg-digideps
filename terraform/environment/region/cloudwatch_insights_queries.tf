resource "aws_cloudwatch_query_definition" "sign_up_matching_errors" {
  name            = "Business Analytics/${local.environment} sign up matching errors"
  log_group_names = [aws_cloudwatch_log_group.opg_digi_deps.name]

  query_string = <<EOF
fields @timestamp
| parse @message "search_terms\":{\"caseNumber\":\"*\",\"clientLastname" as case_number
| parse @message "matching_errors\":{*}" as matching_errors
| parse matching_errors "client_lastname\":*,\"deputy_lastname\":*,\"deputy_postcode\":*" as client_lastname, deputy_lastname, deputy_postcode
| filter @message like 'matching_errors'
| sort @timestamp desc
| display @timestamp, case_number, client_lastname, deputy_lastname, deputy_postcode, @message
EOF
}

resource "aws_cloudwatch_query_definition" "readonly_db_iam_assumptions" {
  name            = "IAM Audit/${local.environment} readonly-db-iam role assumptions"
  log_group_names = ["cloudtrail"]

  query_string = <<EOF
fields @timestamp, eventName, userIdentity.arn, requestParameters.roleArn, sourceIPAddress
| filter eventName = "AssumeRole"
| filter requestParameters.roleArn = "arn:aws:iam::${data.aws_caller_identity.current.account_id}:role/readonly-db-iam-${local.environment}"
| sort @timestamp desc
| limit 50
EOF
}

resource "aws_cloudwatch_query_definition" "readonly_db_iam_queries" {
  name            = "IAM Audit/${local.environment} readonly-db-iam DB queries"
  log_group_names = ["/aws/rds/cluster/api-${local.environment}/postgresql"]

  query_string = <<EOF
fields @timestamp, @message
| filter @message like 'readonly-db-iam-${local.environment}'
| sort @timestamp desc
| limit 50
EOF
}
