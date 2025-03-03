resource "aws_ecs_task_definition" "dr_backup" {
  family                   = "backup-cross-account-${terraform.workspace}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 256
  memory                   = 512
  container_definitions    = "[${local.dr_backup}]"
  task_role_arn            = aws_iam_role.dr_backup.arn
  execution_role_arn       = var.execution_role_arn
  runtime_platform {
    cpu_architecture        = "ARM64"
    operating_system_family = "LINUX"
  }
  tags = merge(var.default_tags,
    { "Role" = "backup-cross-account-${var.environment}" },
  )
}

locals {
  dr_backup = jsonencode({
    cpu       = 0,
    essential = true,
    image     = var.images.dr-backup,
    name      = "backup_cross_account",
    healthCheck = {
      command     = ["CMD-SHELL", "echo healthy || exit 1"],
      startPeriod = 30,
      interval    = 15,
      timeout     = 10,
      retries     = 3
    },
    logConfiguration = {
      logDriver = "awslogs",
      options = {
        awslogs-group         = aws_cloudwatch_log_group.dr_backup.name,
        awslogs-region        = "eu-west-1",
        awslogs-stream-prefix = "backup-cross-account-${var.environment}"
      }
    },
    environment = [
      {
        name  = "KMS_KEY_ID",
        value = aws_kms_key.db_backup.id
      },
      {
        name  = "BACKUP_ACCOUNT",
        value = var.backup_account_id
      },
      {
        name  = "SOURCE_ACCOUNT",
        value = var.account_id
      },
      {
        name  = "BACKUP_ACCOUNT_ROLE",
        value = "arn:aws:iam::${var.backup_account_id}:role/${var.cross_account_role_name}"
      },
      {
        name  = "DB_ID"
        value = "${var.db.name}-${var.environment}"
      },
      {
        name  = "CLUSTER"
        value = "true"
      },
    ]
  })
}

resource "aws_cloudwatch_log_group" "dr_backup" {
  name              = "backup-cross-account-${var.environment}"
  retention_in_days = var.log_retention
  kms_key_id        = var.logs_kms_key_arn
  tags              = var.default_tags
}

# TASK ROLE
resource "aws_iam_role" "dr_backup" {
  assume_role_policy = var.task_role_assume_policy.json
  name               = "dr-backup.${var.environment}"
  tags               = var.default_tags
}

data "aws_iam_policy_document" "dr_backup" {
  statement {
    sid       = "allowAssumeAccess"
    effect    = "Allow"
    resources = ["arn:aws:iam::${var.backup_account_id}:role/${var.cross_account_role_name}"]
    actions = [
      "sts:AssumeRole"
    ]
  }

  statement {
    sid    = "allowKMSAccess"
    effect = "Allow"
    actions = [
      "kms:CreateGrant",
      "kms:DescribeKey"
    ]
    resources = [aws_kms_key.db_backup.arn]
  }

  statement {
    sid    = "allowSnapshotAccess"
    effect = "Allow"
    #trivy:ignore:avd-aws-0057 - needs access to snapshots of any name
    resources = ["*"]
    actions = [
      "rds:CreateClusterDBSnapshot",
      "rds:CreateDBSnapshot",
      "rds:DeleteDBClusterSnapshot",
      "rds:DeleteDBSnapshot",
      "rds:DescribeDBInstances",
      "rds:DescribeDBClusters",
      "rds:DescribeDBClusterSnapshots",
      "rds:DescribeDBSnapshots",
      "rds:ModifyDBClusterSnapshotAttribute",
      "rds:ModifyDBSnapshotAttribute",
      "rds:DescribeDBClusterSnapshotAttributes",
      "rds:DescribeDBSnapshotAttributes",
      "rds:CopyDBClusterSnapshot",
      "rds:CopyDBSnapshot",
      "rds:ListTagsForResource"
    ]
  }
}

resource "aws_iam_role_policy" "dr_backup" {
  name   = "backup-cross-account-task.${var.environment}"
  policy = data.aws_iam_policy_document.dr_backup.json
  role   = aws_iam_role.dr_backup.id
}
