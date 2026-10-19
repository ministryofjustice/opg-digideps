module "ec2messages_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "ec2messages"
  service_short_title = "ec2messages"
  tags                = var.default_tags
  policy              = var.account.name == "development" ? data.aws_iam_policy_document.ec2messages_endpoint.json : ""
}

data "aws_iam_policy_document" "ec2messages_endpoint" {
  statement {
    sid    = "AllowEC2MessagesFromThisAccount"
    effect = "Allow"

    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::${data.aws_caller_identity.current.account_id}:root"]
    }

    actions = [
      "ec2messages:*"
    ]

    resources = ["*"]
  }
}
