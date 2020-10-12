resource "aws_flow_log" "front_flow" {
  iam_role_arn    = aws_iam_role.front_flow.arn
  log_destination = aws_cloudwatch_log_group.front_flow.arn
  traffic_type    = "ALL"
  vpc_id          = data.aws_vpc.vpc.id
}

resource "aws_cloudwatch_log_group" "front_flow" {
  name              = "front-flow-${local.environment}"
  retention_in_days = 1
  tags              = local.default_tags
}

resource "aws_iam_role" "front_flow" {
  name = "front-flow-${local.environment}"

  assume_role_policy = <<EOF
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "",
      "Effect": "Allow",
      "Principal": {
        "Service": "vpc-flow-logs.amazonaws.com"
      },
      "Action": "sts:AssumeRole"
    }
  ]
}
EOF
}

resource "aws_iam_role_policy" "front_flow" {
  name = "front-flow-${local.environment}"
  role = aws_iam_role.front_flow.id

  policy = <<EOF
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Action": [
        "logs:CreateLogGroup",
        "logs:CreateLogStream",
        "logs:PutLogEvents",
        "logs:DescribeLogGroups",
        "logs:DescribeLogStreams"
      ],
      "Effect": "Allow",
      "Resource": "*"
    }
  ]
}
EOF
}
