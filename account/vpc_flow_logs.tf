resource "aws_flow_log" "vpc_flow_logs" {
  iam_role_arn    = aws_iam_role.vpc_flow_logs.arn
  log_destination = aws_cloudwatch_log_group.vpc_flow_logs.arn
  traffic_type    = "ALL"
  vpc_id          = aws_vpc.main.id
}

resource "aws_cloudwatch_log_group" "vpc_flow_logs" {
  name              = "vpc-flow-logs-${local.account.name}"
  kms_key_id        = aws_kms_key.cloudwatch_logs.arn
  retention_in_days = 180
}

resource "aws_flow_log" "vpc_flow_logs_default" {
  iam_role_arn    = aws_iam_role.vpc_flow_logs.arn
  log_destination = aws_cloudwatch_log_group.vpc_flow_logs_default.arn
  traffic_type    = "ALL"
  vpc_id          = data.aws_vpc.default.id
}

resource "aws_cloudwatch_log_group" "vpc_flow_logs_default" {
  name              = "vpc-flow-logs-default-${local.account.name}"
  kms_key_id        = aws_kms_key.cloudwatch_logs.arn
  retention_in_days = 180
}

resource "aws_iam_role" "vpc_flow_logs" {
  name               = "vpc-flow-logs-${local.account.name}"
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
  name   = "vpc-flow-logs-${local.account.name}"
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
