module "ssm_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "ssm"
  service_short_title = "ssm"
  tags                = var.default_tags
  policy              = var.account.name == "development" ? data.aws_iam_policy_document.ssm_endpoint.json : ""
}

data "aws_iam_policy_document" "ssm_endpoint" {
  statement {
    sid    = "AllowSSMFromThisAccount"
    effect = "Allow"

    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::${data.aws_caller_identity.current.account_id}:root"]
    }

    actions = [
      "ssm:*"
    ]

    resources = ["*"]
  }
}
