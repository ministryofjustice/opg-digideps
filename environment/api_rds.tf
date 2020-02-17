data "aws_kms_key" "rds" {
  key_id = "alias/aws/rds"
}

resource "aws_db_instance" "api" {
  count                   = local.account.always_on ? 1 : 0
  name                    = "api"
  identifier              = "api-${local.environment}"
  instance_class          = "db.m3.medium"
  allocated_storage       = "10"
  availability_zone       = "eu-west-1a"
  backup_retention_period = "14"
  backup_window           = "00:00-00:30"
  db_subnet_group_name    = local.account.db_subnet_group
  engine                  = "postgres"
  engine_version          = "9.6"
  kms_key_id              = data.aws_kms_key.rds.arn
  license_model           = "postgresql-license"
  maintenance_window      = "sun:01:00-sun:01:30"
  monitoring_interval     = "0"
  option_group_name       = "default:postgres-9-6"
  parameter_group_name    = "default.postgres9.6"
  port                    = "5432"
  skip_final_snapshot     = "true"
  storage_encrypted       = "true"
  storage_type            = "gp2"
  username                = "digidepsmaster"
  password                = data.aws_secretsmanager_secret_version.database_password.secret_string

  vpc_security_group_ids = [module.api_rds_security_group.id]

  tags = merge(
    local.default_tags,
    {
      "Name" = "api.${local.environment}.${local.account.account_id}.${data.aws_route53_zone.public.name}"
    },
  )

  lifecycle {
    ignore_changes = [password]
  }
}

resource "aws_rds_cluster" "api" {
  count                        = local.account.always_on ? 0 : 1
  cluster_identifier           = "api-${local.environment}"
  engine                       = "aurora-postgresql"
  engine_mode                  = local.account.always_on ? "provisioned" : "serverless"
  engine_version               = "9.6.16"
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

resource "aws_iam_role" "enhanced_monitoring" {
  name               = "rds-enhanced-monitoring"
  assume_role_policy = data.aws_iam_policy_document.enhanced_monitoring.json
}

resource "aws_iam_role_policy_attachment" "enhanced_monitoring" {
  policy_arn = "arn:aws:iam::aws:policy/service-role/AmazonRDSEnhancedMonitoringRole"
  role       = aws_iam_role.enhanced_monitoring.name
}

data "aws_iam_policy_document" "enhanced_monitoring" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["monitoring.rds.amazonaws.com"]
      type        = "Service"
    }
  }
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
