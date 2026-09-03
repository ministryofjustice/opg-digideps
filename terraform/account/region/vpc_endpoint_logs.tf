module "logs_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "logs"
  service_short_title = "logs"
  tags                = var.default_tags
  policy              = var.account.name == "development" ? data.aws_iam_policy_document.logs_endpoint.json : ""
}

data "aws_iam_policy_document" "logs_endpoint" {
  statement {
    sid    = "AllowApprovedBuckets"
    effect = "Allow"
    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::${data.aws_caller_identity.current.account_id}:root"]
    }
    actions = [
      "logs:*",
      "rum:*",
      "monitoring:*"
    ]
    resources = [
      "arn:aws:logs:${data.aws_region.current.region}:${data.aws_caller_identity.current.account_id}:*",
      "arn:aws:rum:${data.aws_region.current.region}:${data.aws_caller_identity.current.account_id}:*",
      "arn:aws:monitoring:${data.aws_region.current.region}:${data.aws_caller_identity.current.account_id}:*"
    ]
  }
}
