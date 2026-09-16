module "ecr_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "ecr.dkr"
  service_short_title = "ecr"
  tags                = var.default_tags
  policy              = var.account.name == "development" ? data.aws_iam_policy_document.ecr_endpoint.json : ""
}

module "ecr_api_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "ecr.api"
  service_short_title = "ecr_api"
  tags                = var.default_tags
  policy              = var.account.name == "development" ? data.aws_iam_policy_document.ecr_endpoint.json : ""
}

data "aws_iam_policy_document" "ecr_endpoint" {
  statement {
    sid    = "AllowROAccessToECR"
    effect = "Allow"
    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::${data.aws_caller_identity.current.account_id}:root"]
    }
    actions = [
      "ecr:Get*",
      "ecr:Describe*",
      "ecr:BatchGetImage"
    ]
    resources = [
      "arn:aws:ecr:${data.aws_region.current.region}:${data.aws_caller_identity.current.account_id}:*",
      "arn:aws:ecr:${data.aws_region.current.region}:${local.management_account_id}:*"
    ]
  }

  statement {
    sid    = "AllowGetAuthToken"
    effect = "Allow"
    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::${data.aws_caller_identity.current.account_id}:root"]
    }
    actions = [
      "ecr:GetAuthorizationToken"
    ]
    resources = ["*"]
  }
}
