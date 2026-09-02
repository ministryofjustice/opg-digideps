# For testing purposes we are going to limit to development initially
resource "aws_vpc_endpoint" "s3_endpoint_vpc" {
  service_name      = "com.amazonaws.eu-west-1.s3"
  vpc_id            = module.network.vpc.id
  vpc_endpoint_type = "Gateway"
  route_table_ids   = module.network.application_subnet_route_tables[*].id
  tags              = merge(var.default_tags, { Name = "s3" })
  policy            = var.account.name == "development" ? data.aws_iam_policy_document.s3_endpoint.json : ""
}

data "aws_iam_policy_document" "s3_endpoint" {
  statement {
    sid    = "AllowApprovedBuckets"
    effect = "Allow"
    principals {
      type        = "*"
      identifiers = ["*"]
    }
    actions = [
      "s3:Get*",
      "s3:List*",
      "s3:Put*"
    ]
    resources = [
      "arn:aws:s3:::pa-uploads-*",
      "arn:aws:s3:::pa-uploads-*/*",
      "arn:aws:s3:::s3-access-logs-opg-digideps-*",
      "arn:aws:s3:::s3-access-logs-opg-digideps-*/*",
      "arn:aws:s3:::opg-cloudtrail-*",
      "arn:aws:s3:::opg-cloudtrail-*/*",
      "arn:aws:s3:::cloudtrail*",
      "arn:aws:s3:::cloudtrail*/*",
      "arn:aws:s3:::digideps*",
      "arn:aws:s3:::digideps*/*",
      "arn:aws:s3:::config-eu-west-*",
      "arn:aws:s3:::config-eu-west-*/*",
      "arn:aws:s3:::alb-logs*",
      "arn:aws:s3:::alb-logs*/*",
      "arn:aws:s3:::alb-athena*",
      "arn:aws:s3:::alb-athena*/*",
    ]
  }
}
