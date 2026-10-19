module "ssmmessages_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "ssmmessages"
  service_short_title = "ssmmessages"
  tags                = var.default_tags
  policy              = var.account.name == "development" ? data.aws_iam_policy_document.ssmmessages_endpoint.json : ""
}

data "aws_iam_policy_document" "ssmmessages_endpoint" {
  statement {
    sid    = "AllowSSMMessagesFromThisAccount"
    effect = "Allow"

    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::${data.aws_caller_identity.current.account_id}:root"]
    }

    actions = [
      "ssmmessages:*"
    ]

    resources = ["*"]
  }
}
