module "backup" {
  source = "./modules/task"
  name   = "backup"

  cluster_name          = aws_ecs_cluster.main.name
  container_definitions = "[${local.backup_container}]"
  tags                  = var.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution_role.arn
  subnet_ids            = data.aws_subnet.private[*].id
  task_role_arn         = data.aws_iam_role.sync.arn
  vpc_id                = data.aws_vpc.vpc.id
  security_group_id     = module.backup_security_group.id
}

locals {
  backup_sg_rules = {
    ecr     = local.common_sg_rules.ecr
    logs    = local.common_sg_rules.logs
    s3      = local.common_sg_rules.s3
    ssm     = local.common_sg_rules.ssm
    ecr_api = local.common_sg_rules.ecr_api
    secrets = local.common_sg_rules.secrets
    rds = {
      port        = 5432
      protocol    = "tcp"
      type        = "egress"
      target_type = "security_group_id"
      target      = module.api_rds_security_group.id
    }
  }
}

module "backup_security_group" {
  source      = "./modules/security_group"
  description = "Backup Service"
  rules       = local.backup_sg_rules
  name        = "backup"
  tags        = var.default_tags
  vpc_id      = data.aws_vpc.vpc.id
  environment = local.environment
}

data "aws_kms_alias" "backup" {
  name     = "alias/backup"
  provider = aws.management
}

locals {
  backup_container = jsonencode(
    {
      name    = "backup",
      image   = local.images.sync,
      command = ["./backup.sh"],
      logConfiguration = {
        logDriver = "awslogs",
        options = {
          awslogs-group         = aws_cloudwatch_log_group.opg_digi_deps.name,
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = "backup"
        }
      },
      secrets = [{
        name      = "POSTGRES_PASSWORD",
        valueFrom = data.aws_secretsmanager_secret.database_password.arn
      }],
      environment = [{
        name  = "S3_BUCKET",
        value = data.aws_s3_bucket.backup.bucket
        },
        {
          name  = "S3_OPTS",
          value = "--sse=aws:kms --sse-kms-key-id=${data.aws_kms_alias.backup.target_key_arn} --grants=read=id=${var.shared_environment_variables["canonical_id_preproduction"]},id=${var.shared_environment_variables["canonical_id_production"]}"
        },
        {
          name  = "S3_PREFIX",
          value = local.environment
        },
        {
          name  = "POSTGRES_DATABASE",
          value = local.db.name
        },
        {
          name  = "POSTGRES_HOST",
          value = local.db.endpoint
        },
        {
          name  = "POSTGRES_PORT",
          value = tostring(local.db.port)
        },
        {
          name  = "POSTGRES_USER",
          value = local.db.username
        }
      ]
    }
  )
}

# Role that we use for backup and restore operations
data "aws_iam_role" "sync" {
  name = "sync"
}

# Bucket that we backup and restore from
data "aws_s3_bucket" "backup" {
  bucket   = "backup.complete-deputy-report.service.gov.uk"
  provider = aws.management
}