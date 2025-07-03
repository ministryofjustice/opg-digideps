# To monitor the VPC that we use
resource "aws_flow_log" "vpc_flow_logs" {
  iam_role_arn    = aws_iam_role.vpc_flow_logs.arn
  log_destination = aws_cloudwatch_log_group.vpc_flow_logs.arn
  traffic_type    = "ALL"
  vpc_id          = aws_vpc.main.id
}

resource "aws_cloudwatch_log_group" "vpc_flow_logs" {
  name              = "vpc-flow-logs-${var.account.name}"
  kms_key_id        = module.logs_kms.eu_west_1_target_key_arn
  retention_in_days = 180
}

resource "aws_cloudwatch_log_anomaly_detector" "vpc_flow_logs" {
  detector_name           = "vpc-flow-logs"
  log_group_arn_list      = [aws_cloudwatch_log_group.vpc_flow_logs.arn]
  anomaly_visibility_time = 14
  evaluation_frequency    = "TEN_MIN"
  enabled                 = "true"
  kms_key_id              = module.anomaly_kms.eu_west_1_target_key_id
}

# To monitor the default VPC. Logs should be empty.
resource "aws_flow_log" "vpc_flow_logs_default" {
  iam_role_arn    = aws_iam_role.vpc_flow_logs.arn
  log_destination = aws_cloudwatch_log_group.vpc_flow_logs_default.arn
  traffic_type    = "ALL"
  vpc_id          = data.aws_vpc.default.id
}

resource "aws_cloudwatch_log_group" "vpc_flow_logs_default" {
  name              = "vpc-flow-logs-default-${var.account.name}"
  kms_key_id        = module.logs_kms.eu_west_1_target_key_arn
  retention_in_days = 180
}

# VPC Flow Logs Role and Permissions
resource "aws_iam_role" "vpc_flow_logs" {
  name               = "vpc-flow-logs-${var.account.name}"
  assume_role_policy = data.aws_iam_policy_document.vpc_flow_logs_role_assume_role_policy.json
}

data "aws_iam_policy_document" "vpc_flow_logs_role_assume_role_policy" {
  statement {
    actions = ["sts:AssumeRole"]

    principals {
      type        = "Service"
      identifiers = ["vpc-flow-logs.amazonaws.com"]
    }
  }
}

resource "aws_iam_role_policy" "vpc_flow_logs" {
  name   = "vpc-flow-logs-${var.account.name}"
  role   = aws_iam_role.vpc_flow_logs.id
  policy = data.aws_iam_policy_document.vpc_flow_logs_role_policy.json
}

data "aws_iam_policy_document" "vpc_flow_logs_role_policy" {
  statement {
    actions = [
      "logs:CreateLogGroup",
      "logs:CreateLogStream",
      "logs:PutLogEvents",
      "logs:DescribeLogGroups",
      "logs:DescribeLogStreams"
    ]
    # This is as defined in the AWS Documentation. See https://docs.aws.amazon.com/vpc/latest/userguide/flow-logs-cwl.html
    #tfsec:ignore:aws-iam-no-policy-wildcards
    resources = ["*"]
    effect    = "Allow"
  }
}
