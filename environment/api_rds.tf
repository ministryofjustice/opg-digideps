resource "aws_security_group" "api_rds" {
  name        = "rds-api-${local.environment}"
  description = "api rds access"
  vpc_id      = data.aws_vpc.vpc.id

  tags = merge(
    local.default_tags,
    {
      "Name" = "rds-api-${local.environment}"
    },
  )
}

resource "aws_security_group_rule" "api_rds_task_in" {
  type                     = "ingress"
  protocol                 = "tcp"
  from_port                = 5432
  to_port                  = 5432
  security_group_id        = aws_security_group.api_rds.id
  source_security_group_id = aws_security_group.api_task.id
}

resource "aws_security_group_rule" "api_rds_sync_in" {
  type                     = "ingress"
  protocol                 = "tcp"
  from_port                = 5432
  to_port                  = 5432
  security_group_id        = aws_security_group.api_rds.id
  source_security_group_id = aws_security_group.sync.id
}

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

  vpc_security_group_ids = [
    aws_security_group.api_rds.id,
  ]

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

resource "aws_route53_record" "api_postgres" {
  name    = "postgres"
  type    = "CNAME"
  zone_id = aws_route53_zone.internal.id
  records = [aws_db_instance.api.address]
  ttl     = 300
}
