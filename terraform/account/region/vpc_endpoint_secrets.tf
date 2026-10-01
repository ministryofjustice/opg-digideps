module "secrets_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "secretsmanager"
  service_short_title = "secrets"
  tags                = var.default_tags
  policy              = var.account.name == "development" ? data.aws_iam_policy_document.secrets_endpoint.json : ""
}

data "aws_iam_policy_document" "secrets_endpoint" {
  statement {
    sid    = "AllowSecrets"
    effect = "Allow"
    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::${data.aws_caller_identity.current.account_id}:root"]
    }
    actions = [
      "secretsmanager:GetSecretValue",
      "secretsmanager:DescribeSecret"
    ]
    resources = [
      "arn:aws:secretsmanager:${data.aws_region.current.region}:${data.aws_caller_identity.current.account_id}:*"
    ]
  }
}
