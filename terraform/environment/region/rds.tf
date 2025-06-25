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

resource "aws_route53_record" "api_postgres" {
  name    = "postgres"
  type    = "CNAME"
  zone_id = aws_route53_zone.internal.id
  records = [local.db.endpoint]
  ttl     = 300
}

data "aws_caller_identity" "current" {}


# Allow the Operator Role to Connect

data "aws_iam_role" "operator" {
  name = "operator"
}


data "aws_iam_policy_document" "operator_rds_connect" {
  statement {
    sid    = "AllowRdsConnect"
    effect = "Allow"

    actions = ["rds-db:connect"]

    resources = [
      "${module.api_aurora[0].cluster_arn}/dbuser/readonly_sql_user"
    ]
  }
}

resource "aws_iam_policy" "operator_rds_connect" {
  name        = "operator-rds-readonly-access"
  description = "Allow operator role to connect to RDS via IAM Auth."
  policy      = data.aws_iam_policy_document.operator_rds_connect.json
}

resource "aws_iam_role_policy_attachment" "operator_rds_connect" {
  role       = data.aws_iam_role.operator.name
  policy_arn = aws_iam_policy.operator_rds_connect.arn
}
