module "rds_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "rds"
  service_short_title = "rds"
  tags                = var.default_tags
  policy              = var.account.name == "development" ? data.aws_iam_policy_document.rds_api_endpoint.json : ""
}

data "aws_iam_policy_document" "rds_api_endpoint" {
  statement {
    sid    = "AllowApprovedRDSOperations"
    effect = "Allow"
    principals {
      type        = "*"
      identifiers = ["*"]
    }
    actions = [
      "rds:Describe*",
      "rds:*Snapshot*"
    ]
    resources = ["*"]
  }
}
