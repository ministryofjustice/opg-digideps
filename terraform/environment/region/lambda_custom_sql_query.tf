locals {
  custom_sql_lambda_env_vars = {
    ENVIRONMENT       = local.environment
    DATABASE_USERNAME = "custom_sql_user"
    DATABASE_HOSTNAME = local.db.endpoint
    DATABASE_NAME     = local.db.name
    DATABASE_PORT     = tostring(local.db.port)
  }
}

data "aws_iam_role" "custom_sql_user" {
  name = "custom-sql-role-${var.account.name}"
}

module "lamdba_custom_sql_query" {
  source                = "./modules/lambda"
  lambda_name           = "custom-sql-query-${local.environment}"
  description           = "Function to run custom sql queries"
  environment_variables = local.custom_sql_lambda_env_vars
  image_uri             = local.images.custom-sql
  ecr_arn               = data.aws_ecr_repository.images["custom-sql-lambda"].arn
  tags                  = var.default_tags
  environment           = local.environment
  aws_subnet_ids        = data.aws_subnet.private[*].id
  memory                = 1024
  vpc_id                = data.aws_vpc.vpc.id
  secrets               = []
  logs_kms_key_arn      = data.aws_kms_alias.cloudwatch_application_logs_encryption.arn
  secrets_kms_key_arn   = data.aws_kms_alias.cloudwatch_application_secret_encryption.target_key_arn
}

resource "aws_security_group_rule" "lambda_custom_sql_query_to_front" {
  type                     = "egress"
  protocol                 = "tcp"
  from_port                = 5432
  to_port                  = 5432
  source_security_group_id = module.api_rds_security_group.id
  security_group_id        = module.lamdba_custom_sql_query.lambda_sg.id
  description              = "Outbound lambda custom_sql to database"
}

resource "aws_security_group_rule" "lambda_custom_sql_query_to_secrets_endpoint" {
  type                     = "egress"
  protocol                 = "tcp"
  from_port                = 443
  to_port                  = 443
  source_security_group_id = data.aws_security_group.secrets_endpoint.id
  security_group_id        = module.lamdba_custom_sql_query.lambda_sg.id
  description              = "Outbound lambda custom_sql to secrets endpoint"
}

resource "aws_lambda_permission" "allow_invoke_from_users" {
  statement_id  = "AllowExecutionFromCLI"
  action        = "lambda:InvokeFunction"
  function_name = module.lamdba_custom_sql_query.lambda.function_name
  principal     = data.aws_iam_role.custom_sql_user.arn
}

resource "aws_iam_role_policy" "custom_sql_query_secretsmanager" {
  name   = "custom-sql-query-secretsmanager.${local.environment}"
  policy = data.aws_iam_policy_document.custom_sql_query_secretsmanager.json
  role   = module.lamdba_custom_sql_query.lambda_role.id
}

data "aws_iam_policy_document" "custom_sql_query_secretsmanager" {
  statement {
    sid    = "AllowQuerySecretsmanagerSecrets"
    effect = "Allow"
    actions = [
      "secretsmanager:GetSecretValue"
    ]
    resources = [
      data.aws_secretsmanager_secret.custom_sql_db_password.arn,
      data.aws_secretsmanager_secret.custom_sql_users.arn
    ]
  }

  statement {
    sid    = "AllowPutSecretsmanagerSecrets"
    effect = "Allow"
    actions = [
      "secretsmanager:PutSecretValue"
    ]
    resources = [
      data.aws_secretsmanager_secret.custom_sql_users.arn
    ]
  }

  statement {
    sid    = "DecryptSecretKMS"
    effect = "Allow"
    actions = [
      "kms:Decrypt"
    ]
    resources = [
      data.aws_kms_alias.cloudwatch_application_secret_encryption.target_key_arn
    ]
  }
}
