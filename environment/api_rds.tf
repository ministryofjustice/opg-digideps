data "aws_kms_key" "rds" {
  key_id = "alias/aws/rds"
}

resource "aws_db_instance" "api" {
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
  cluster_identifier      = "api-${local.environment}"
  engine                  = "aurora-postgresql"
  engine_mode             = local.account.state_source == "development" ? "serverless" : "provisioned"
  availability_zones      = ["eu-west-1a", "eu-west-1b", "eu-west-1c"]
  database_name           = "api"
  master_username         = "digidepsmaster"
  master_password         = data.aws_secretsmanager_secret_version.database_password.secret_string
  backup_retention_period = 14
  preferred_backup_window = "07:00-09:00"
  db_subnet_group_name    = local.account.db_subnet_group
  kms_key_id              = data.aws_kms_key.rds.arn
  storage_encrypted       = true
  vpc_security_group_ids  = [module.api_rds_security_group.id]
  deletion_protection     = local.account.state_source == "development" ? false : true
  enable_http_endpoint    = local.account.state_source == "development" ? true : false

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

resource "aws_route53_record" "api_postgres" {
  name    = "postgres"
  type    = "CNAME"
  zone_id = aws_route53_zone.internal.id
  records = [aws_db_instance.api.address]
  ttl     = 300
}
