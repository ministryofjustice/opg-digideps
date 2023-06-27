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
