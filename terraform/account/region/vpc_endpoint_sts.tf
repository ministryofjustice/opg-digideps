module "sts_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "sts"
  service_short_title = "sts"
  tags                = var.default_tags
  policy              = var.account.name == "development" ? data.aws_iam_policy_document.sts_endpoint.json : ""
}

data "aws_iam_policy_document" "sts_endpoint" {
  statement {
    sid    = "AllowSTSFromThisAccount"
    effect = "Allow"

    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::${data.aws_caller_identity.current.account_id}:root"]
    }

    actions = [
      "sts:*"
    ]

    resources = ["*"]
  }
}
