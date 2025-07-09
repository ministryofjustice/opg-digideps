##### Application Log Group #####
resource "aws_cloudwatch_log_group" "opg_digi_deps" {
  name              = local.environment
  retention_in_days = 180
  kms_key_id        = data.aws_kms_alias.cloudwatch_application_logs_encryption.arn
  tags              = var.default_tags
}

resource "aws_cloudwatch_log_anomaly_detector" "vpc_flow_logs" {
  detector_name           = local.environment
  log_group_arn_list      = [aws_cloudwatch_log_group.opg_digi_deps.arn]
  anomaly_visibility_time = 14
  evaluation_frequency    = "TEN_MIN"
  enabled                 = "true"
  kms_key_id              = data.aws_kms_alias.cloudwatch_anomaly_logs_encryption.target_key_arn
}

resource "aws_cloudwatch_log_data_protection_policy" "application_logs" {
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name
  policy_document = jsonencode(merge(
    jsondecode(file("${path.root}/region/cloudwatch_log_data_protection_policy/cloudwatch_log_data_protection_policy.json")),
    {
      Name = "data-protection-app-logs-${local.environment}"
    }
  ))
}

##### Audit Log Group #####
resource "aws_cloudwatch_log_group" "audit" {
  name       = "audit-${local.environment}"
  kms_key_id = data.aws_kms_alias.cloudwatch_application_logs_encryption.arn
  tags       = var.default_tags
}

##### Shared Application KMS key for logs #####
data "aws_kms_alias" "cloudwatch_application_logs_encryption" {
  name = "alias/digideps_application_logs_encryption_key"
}

##### Anomaly Detection KMS key #####
data "aws_kms_alias" "cloudwatch_anomaly_logs_encryption" {
  name = "alias/digideps_anomaly_encryption_key"
}
