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
  security_group_id     = module.db_access_task_security_group.id
}

data "aws_kms_alias" "backup" {
  name     = "alias/backup"
  provider = aws.management
}

locals {
  backup_container = jsonencode(
    {
      name    = "backup",
      image   = local.images.orchestration,
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
      environment = concat(local.api_single_db_tasks_base_variables,
        [
          {
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
          }
      ])
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
