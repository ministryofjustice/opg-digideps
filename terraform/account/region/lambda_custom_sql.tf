locals {
  lambda_custom_sql_name = "custom-sql-query"
  custom_sql_lambda_env_vars = {
    ENVIRONMENT       = var.account.name
    DATABASE_USERNAME = "custom_sql_user"
  }

  read_secret_names = [
    "custom-sql-users",
    "custom-sql-db-password"
  ]

  read_secret_arns = flatten([
    for env in var.account.environments : [
      for name, arn in module.environment_secrets[env].secret_arns :
      arn if contains(local.read_secret_names, name)
    ]
  ])

  write_secret_names = [
    "custom-sql-users",
  ]

  write_secret_arns = flatten([
    for env in var.account.environments : [
      for name, arn in module.environment_secrets[env].secret_arns :
      arn if contains(local.write_secret_names, name)
    ]
  ])
}

# ===== LAMBDA FUNCTION AND LOG GROUP =====
resource "aws_lambda_function" "custom_sql_query" {
  function_name = "${local.lambda_custom_sql_name}-${var.account.name}"
  description   = "Function to run custom sql queries"
  image_uri     = "${data.aws_ecr_repository.custom_sql_query.repository_url}:latest"
  package_type  = "Image"
  role          = aws_iam_role.custom_sql_query_task_role.arn
  timeout       = 600
  memory_size   = 1024
  depends_on    = [aws_cloudwatch_log_group.custom_sql_query]

  vpc_config {
    subnet_ids         = aws_subnet.private[*].id
    security_group_ids = [aws_security_group.custom_sql_query.id]
  }

  tracing_config {
    mode = "Active"
  }

  dynamic "environment" {
    for_each = length(keys(local.custom_sql_lambda_env_vars)) == 0 ? [] : [true]
    content {
      variables = local.custom_sql_lambda_env_vars
    }
  }
}

resource "aws_cloudwatch_log_group" "custom_sql_query" {
  name              = "/aws/lambda/${local.lambda_custom_sql_name}"
  retention_in_days = 14
  kms_key_id        = module.logs_kms.eu_west_1_target_key_arn
  tags = merge(
    var.default_tags,
    { Name = "${var.account.name}-custom-sql-query" },
  )
}

# ===== ALLOW INVOKE FROM USERS =====
# This is the role that the developers assume
data "aws_iam_role" "custom_sql_developer_role" {
  name = "custom-sql-role-${var.account.name}"
}

resource "aws_lambda_permission" "allow_invoke_from_users" {
  statement_id  = "AllowExecutionFromCLI"
  action        = "lambda:InvokeFunction"
  function_name = aws_lambda_function.custom_sql_query.function_name
  principal     = data.aws_iam_role.custom_sql_developer_role.arn
}

# ===== CUSTOM SQL SECURITY GROUP =====
# DB rules are applied to this from the environment terraform for each environment
resource "aws_security_group" "custom_sql_query" {
  name        = "${var.account.name}-${local.lambda_custom_sql_name}"
  vpc_id      = aws_vpc.main.id
  description = "Custom SQL Shared Lambda"

  lifecycle {
    create_before_destroy = true
  }

  revoke_rules_on_delete = true

  tags = merge(
    var.default_tags,
    { Name = "${var.account.name}-custom-sql-query" },
  )
}

data "aws_security_group" "secrets_endpoint" {
  tags   = { Name = "secrets_endpoint" }
  vpc_id = aws_vpc.main.id
}

resource "aws_security_group_rule" "lambda_custom_sql_query_to_secrets_endpoint" {
  type                     = "egress"
  protocol                 = "tcp"
  from_port                = 443
  to_port                  = 443
  source_security_group_id = data.aws_security_group.secrets_endpoint.id
  security_group_id        = aws_security_group.custom_sql_query.id
  description              = "Outbound lambda custom_sql to secrets endpoint"
}

# ===== LAMBDA TASK ROLE PERMISSIONS =====
resource "aws_iam_role" "custom_sql_query_task_role" {
  name               = local.lambda_custom_sql_name
  assume_role_policy = data.aws_iam_policy_document.lambda_assume.json
  lifecycle {
    create_before_destroy = true
  }
  tags = merge(
    var.default_tags,
    { Name = "${var.account.name}-custom-sql-query" },
  )
}

data "aws_iam_policy_document" "lambda_assume" {
  statement {
    actions = ["sts:AssumeRole"]

    principals {
      type        = "Service"
      identifiers = ["lambda.amazonaws.com"]
    }
  }
}

# ====== XRAY POLICY =====
resource "aws_iam_role_policy_attachment" "aws_xray_write_only_access" {
  role       = aws_iam_role.custom_sql_query_task_role.name
  policy_arn = data.aws_iam_policy.aws_xray_write_only_access.arn
}

data "aws_iam_policy" "aws_xray_write_only_access" {
  arn = "arn:aws:iam::aws:policy/AWSXrayWriteOnlyAccess"
}

resource "aws_iam_role_policy" "custom_sql_query" {
  name   = "custom-sql-lambda-${var.account.name}"
  role   = aws_iam_role.custom_sql_query_task_role.id
  policy = data.aws_iam_policy_document.custom_sql_query.json
}

# ===== MAIN POLICY =====
data "aws_ecr_repository" "custom_sql_query" {
  name     = "digideps/custom-sql-lambda"
  provider = aws.management
}

data "aws_iam_policy_document" "custom_sql_query" {
  statement {
    sid       = "LogsAccess"
    effect    = "Allow"
    resources = [aws_cloudwatch_log_group.custom_sql_query.arn]
    actions = [
      "logs:CreateLogStream",
      "logs:PutLogEvents",
      "logs:DescribeLogStreams"
    ]
  }

  statement {
    sid       = "ECRAccess"
    effect    = "Allow"
    resources = [data.aws_ecr_repository.custom_sql_query.arn]
    actions = [
      "ecr:GetRepositoryPolicy",
      "ecr:GetDownloadUrlForLayer",
      "ecr:BatchGetImage",
      "ecr:BatchCheckLayerAvailability",
      "ecr:GetAuthorizationToken",
      "ecr:BatchGetImage",
      "ecr:DescribeImages",
      "ecr:DescribeRepositories",
      "ecr:ListImages",
      "ecr:InitiateLayerUpload"
    ]
  }

  statement {
    sid       = "GetSecretsManagerAccess"
    effect    = "Allow"
    resources = local.read_secret_arns
    actions = [
      "secretsmanager:GetSecretValue"
    ]
  }

  statement {
    sid    = "PutSecretsmanagerAccess"
    effect = "Allow"
    actions = [
      "secretsmanager:PutSecretValue"
    ]
    resources = local.write_secret_arns
  }

  statement {
    sid    = "DecryptSecretKMS"
    effect = "Allow"
    actions = [
      "kms:Decrypt"
    ]
    resources = [
      module.secret_kms.eu_west_1_target_key_arn
    ]
  }
}

# Permissions needed so that lambda can be part of VPC and create log groups etc
resource "aws_iam_role_policy_attachment" "vpc_access_execution_role" {
  role       = aws_iam_role.custom_sql_query_task_role.name
  policy_arn = "arn:aws:iam::aws:policy/service-role/AWSLambdaVPCAccessExecutionRole"
}
