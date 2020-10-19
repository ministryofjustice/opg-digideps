data "aws_kms_key" "rds" {
  key_id = "alias/aws/rds"
}


module "api_aurora" {
  source                 = "./aurora"
  aurora_serverless      = local.account.aurora_serverless
  account_id             = data.aws_caller_identity.current.account_id
  apply_immediately      = local.account.deletion_protection ? false : true
  cluster_identifier     = "api"
  db_subnet_group_name   = local.account.db_subnet_group
  deletion_protection    = local.account.deletion_protection ? true : false
  database_name          = "api"
  master_username        = "digidepsmaster"
  master_password        = data.aws_secretsmanager_secret_version.database_password.secret_string
  instance_count         = local.account.aurora_instance_count
  instance_class         = local.account.name == "development" ? "db.t3.medium" : "db.r5.2xlarge"
  kms_key_id             = data.aws_kms_key.rds.arn
  skip_final_snapshot    = local.account.deletion_protection ? false : true
  vpc_security_group_ids = [module.api_rds_security_group.id]
  tags                   = local.default_tags
}

resource "aws_db_instance" "api" {
  count                       = local.account.always_on ? 1 : 0
  name                        = "api"
  identifier                  = "api-${local.environment}"
  instance_class              = "db.m3.medium"
  allocated_storage           = "10"
  availability_zone           = "eu-west-1a"
  backup_retention_period     = local.account.backup_retention_period
  backup_window               = "00:00-00:30"
  db_subnet_group_name        = local.account.db_subnet_group
  engine                      = "postgres"
  engine_version              = local.account.psql_engine_version
  kms_key_id                  = data.aws_kms_key.rds.arn
  license_model               = "postgresql-license"
  maintenance_window          = "sun:01:00-sun:02:30"
  monitoring_interval         = "0"
  port                        = "5432"
  skip_final_snapshot         = false
  storage_encrypted           = true
  storage_type                = "gp2"
  username                    = "digidepsmaster"
  password                    = data.aws_secretsmanager_secret_version.database_password.secret_string
  deletion_protection         = true
  delete_automated_backups    = false
  auto_minor_version_upgrade  = false
  final_snapshot_identifier   = "api-${local.environment}-final"
  vpc_security_group_ids      = [module.api_rds_security_group.id]
  allow_major_version_upgrade = true

  tags = merge(
    local.default_tags,
    {
      "Name" = "api.${local.environment}.${local.account.account_id}.${data.aws_route53_zone.public.name}"
    },
  )

  lifecycle {
    ignore_changes  = [password]
    prevent_destroy = true
  }
}

resource "aws_cloudwatch_log_group" "api_cluster" {
  name              = "/aws/rds/cluster/api-${local.environment}/postgresql"
  retention_in_days = 180
  tags              = local.default_tags
}

data "aws_iam_role" "enhanced_monitoring" {
  name = "rds-enhanced-monitoring"
}

locals {
  db = {
    endpoint = local.account.always_on ? aws_db_instance.api[0].address : module.api_aurora.endpoint
    port     = local.account.always_on ? aws_db_instance.api[0].port : module.api_aurora.port
    name     = local.account.always_on ? aws_db_instance.api[0].name : module.api_aurora.name
    username = local.account.always_on ? aws_db_instance.api[0].username : module.api_aurora.master_username
  }
}

resource "aws_route53_record" "api_postgres" {
  name    = "postgres"
  type    = "CNAME"
  zone_id = aws_route53_zone.internal.id
  records = [local.db.endpoint]
  ttl     = 300
}

data "aws_caller_identity" "current" {}
