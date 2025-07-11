module "api_aurora" {
  source                              = "./modules/aurora"
  count                               = 1
  aurora_serverless                   = var.account.aurora_serverless
  account_id                          = data.aws_caller_identity.current.account_id
  apply_immediately                   = var.account.deletion_protection ? false : true
  cluster_identifier                  = "api"
  ca_cert_identifier                  = "rds-ca-rsa2048-g1"
  db_subnet_group_name                = var.account.db_subnet_group
  database_name                       = "api"
  engine_version                      = var.account.psql_engine_version
  master_username                     = "digidepsmaster"
  master_password                     = data.aws_secretsmanager_secret_version.database_password.secret_string
  instance_count                      = var.account.aurora_instance_count
  instance_class                      = "db.t3.medium"
  kms_key_id                          = data.aws_kms_key.rds.arn
  skip_final_snapshot                 = var.account.deletion_protection ? false : true
  vpc_security_group_ids              = [module.api_rds_security_group.id]
  deletion_protection                 = var.account.deletion_protection ? true : false
  tags                                = var.default_tags
  log_group                           = aws_cloudwatch_log_group.api_cluster.name
  iam_database_authentication_enabled = true
}

locals {
  db = {
    endpoint = module.api_aurora[0].endpoint
    port     = module.api_aurora[0].port
    name     = module.api_aurora[0].name
    username = module.api_aurora[0].master_username
  }
}

data "aws_kms_key" "rds" {
  key_id = "alias/aws/rds"
}

resource "aws_cloudwatch_log_group" "api_cluster" {
  name              = "/aws/rds/cluster/api-${local.environment}/postgresql"
  kms_key_id        = data.aws_kms_alias.cloudwatch_application_logs_encryption.arn
  retention_in_days = 180
  tags              = var.default_tags
}

resource "aws_cloudwatch_log_anomaly_detector" "api_cluster" {
  detector_name           = "postgresql"
  log_group_arn_list      = [aws_cloudwatch_log_group.api_cluster.arn]
  anomaly_visibility_time = 14
  evaluation_frequency    = "TEN_MIN"
  enabled                 = "true"
  kms_key_id              = data.aws_kms_alias.cloudwatch_anomaly_logs_encryption.target_key_arn
}

resource "aws_route53_record" "api_postgres" {
  name    = "postgres"
  type    = "CNAME"
  zone_id = aws_route53_zone.internal.id
  records = [local.db.endpoint]
  ttl     = 300
}

data "aws_caller_identity" "current" {}


# Allow the Operator Role to Connect via another Role

data "aws_iam_role" "operator" {
  name = "operator"
}

data "aws_iam_policy_document" "database_readonly_assume" {
  statement {
    sid     = "AllowAssume"
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      type        = "AWS"
      identifiers = [data.aws_iam_role.operator.arn]
    }
  }
}

resource "aws_iam_role" "database_readonly_access" {
  name               = "readonly-db-iam-${local.environment}"
  assume_role_policy = data.aws_iam_policy_document.database_readonly_assume.json
  tags               = var.default_tags
}

data "aws_iam_policy_document" "database_readonly_connect" {
  statement {
    sid     = "AllowRdsConnect"
    effect  = "Allow"
    actions = ["rds-db:connect"]

    resources = [
      "arn:aws:rds-db:eu-west-1:${data.aws_caller_identity.current.account_id}:dbuser:${module.api_aurora[0].cluster_resource_id}/readonly-db-iam-${local.environment}"
    ]
  }
}

resource "aws_iam_policy" "database_readonly_connect" {
  name        = "database-readonly-access-${local.environment}"
  description = "Allow database-readonly-access role to connect to RDS via IAM Auth."
  policy      = data.aws_iam_policy_document.database_readonly_connect.json
}

resource "aws_iam_role_policy_attachment" "database_readonly_connect_attach" {
  role       = aws_iam_role.database_readonly_access.name
  policy_arn = aws_iam_policy.database_readonly_connect.arn
}
