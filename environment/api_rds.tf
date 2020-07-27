data "aws_kms_key" "rds" {
  key_id = "alias/aws/rds"
}

data "terraform_remote_state" "previous_workspace" {
  count     = local.account.copy_version_from == "NonApplicable" ? 0 : 1
  backend   = "s3"
  workspace = local.account.copy_version_from
  config = {
    bucket         = "opg.terraform.state"
    key            = "opg-digi-deps-infrastructure/terraform.tfstate"
    encrypt        = true
    region         = "eu-west-1"
    role_arn       = "arn:aws:iam::311462405659:role/${var.DEFAULT_ROLE}"
    dynamodb_table = "remote_lock"
  }
}

locals {
  engine_version = local.account.copy_version_from == "NonApplicable" ? "9.6" : data.terraform_remote_state.previous_workspace[0].outputs["db_engine_version"]
}


resource "aws_db_instance" "api" {
  count                      = local.account.always_on ? 1 : 0
  name                       = "api"
  identifier                 = "api-${local.environment}"
  instance_class             = "db.m3.medium"
  allocated_storage          = "10"
  availability_zone          = "eu-west-1a"
  backup_retention_period    = "14"
  backup_window              = "00:00-00:30"
  db_subnet_group_name       = local.account.db_subnet_group
  engine                     = "postgres"
  engine_version             = local.engine_version
  kms_key_id                 = data.aws_kms_key.rds.arn
  license_model              = "postgresql-license"
  maintenance_window         = "sun:01:00-sun:02:30"
  monitoring_interval        = "0"
  option_group_name          = "default:postgres-9-6"
  parameter_group_name       = "default.postgres9.6"
  port                       = "5432"
  skip_final_snapshot        = false
  storage_encrypted          = true
  storage_type               = "gp2"
  username                   = "digidepsmaster"
  password                   = data.aws_secretsmanager_secret_version.database_password.secret_string
  deletion_protection        = true
  delete_automated_backups   = false
  auto_minor_version_upgrade = local.account.copy_version_from == "NonApplicable" ? true : false
  final_snapshot_identifier  = "api-${local.environment}-final"


  vpc_security_group_ids = [module.api_rds_security_group.id]

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

resource "aws_rds_cluster" "api" {
  count                        = local.account.always_on ? 0 : 1
  cluster_identifier           = "api-${local.environment}"
  engine                       = "aurora-postgresql"
  engine_mode                  = local.account.always_on ? "provisioned" : "serverless"
  engine_version               = "10.7"
  availability_zones           = ["eu-west-1a", "eu-west-1b", "eu-west-1c"]
  database_name                = "api"
  master_username              = "digidepsmaster"
  master_password              = data.aws_secretsmanager_secret_version.database_password.secret_string
  skip_final_snapshot          = true
  backup_retention_period      = 14
  preferred_backup_window      = "07:00-09:00"
  db_subnet_group_name         = local.account.db_subnet_group
  kms_key_id                   = data.aws_kms_key.rds.arn
  storage_encrypted            = true
  vpc_security_group_ids       = [module.api_rds_security_group.id]
  deletion_protection          = local.account.always_on ? true : false
  enable_http_endpoint         = local.account.always_on ? false : true
  preferred_maintenance_window = "sun:01:00-sun:01:30"

  scaling_configuration {
    seconds_until_auto_pause = 900
  }

  depends_on = [aws_cloudwatch_log_group.api_cluster]

  tags = merge(
    local.default_tags,
    {
      "Name" = "api.${local.environment}.${local.account.account_id}.${data.aws_route53_zone.public.name}"
    },
  )

  lifecycle {
    ignore_changes = [engine_version, master_password]
  }
}

resource "aws_cloudwatch_log_group" "api_cluster" {
  name              = "/aws/rds/cluster/api-${local.environment}/postgresql"
  retention_in_days = 180
}

# resource "aws_rds_cluster_instance" "api" {
#   count                        = local.account.always_on ? 1 : 0
#   identifier_prefix            = "api-${local.environment}-"
#   cluster_identifier           = aws_rds_cluster.api.id
#   instance_class               = "db.r4.large"
#   engine                       = aws_rds_cluster.api.engine
#   engine_version               = aws_rds_cluster.api.engine_version
#   performance_insights_enabled = true
#   monitoring_role_arn          = aws_iam_role.enhanced_monitoring.arn
#   monitoring_interval          = 60
#   apply_immediately            = true
#   tags                         = local.default_tags

#   lifecycle {
#     create_before_destroy = true
#   }
# }

data "aws_iam_role" "enhanced_monitoring" {
  name = "rds-enhanced-monitoring"
}

locals {
  db = {
    endpoint = local.account.always_on ? aws_db_instance.api[0].address : aws_rds_cluster.api[0].endpoint
    port     = local.account.always_on ? aws_db_instance.api[0].port : aws_rds_cluster.api[0].port
    name     = local.account.always_on ? aws_db_instance.api[0].name : aws_rds_cluster.api[0].database_name
    username = local.account.always_on ? aws_db_instance.api[0].username : aws_rds_cluster.api[0].master_username
  }
}

resource "aws_route53_record" "api_postgres" {
  name    = "postgres"
  type    = "CNAME"
  zone_id = aws_route53_zone.internal.id
  records = [local.db.endpoint]
  ttl     = 300
}
