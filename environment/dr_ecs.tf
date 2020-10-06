resource "aws_ecs_task_definition" "dr_backup" {
  family                   = "dr-backup-${terraform.workspace}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 256
  memory                   = 512
  container_definitions    = "[${local.dr_backup}]"
  task_role_arn            = aws_iam_role.dr_backup.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags = merge(local.default_tags,
    { "Role" = "dr-backup-${local.environment}" },
  )
}

locals {
  dr_backup = jsonencode({
    cpu       = 0,
    essential = true,
    image     = local.images.drbackup,
    name      = "dr_backup",
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
        awslogs-stream-prefix = "dr-backup-${local.environment}"
      }
    },
    environment = [
      {
        name  = "KMS_KEY_ID",
        value = aws_kms_key.db_backup.id
      },
      {
        name  = "BACKUP_ACCOUNT",
        value = local.backup_account_id
      },
      {
        name  = "SOURCE_ACCOUNT",
        value = local.account.account_id
      },
      {
        name  = "BACKUP_ACCOUNT_ROLE",
        value = aws_iam_role.cross_acc_backup.arn
      },
      {
        name  = "DB_ID"
        value = local.db.name
      },
    ]
  })
}

resource "aws_cloudwatch_log_group" "dr_backup" {
  name              = "dr-backup-${local.environment}"
  retention_in_days = 180
  tags              = local.default_tags
}

//TASK ROLE

resource "aws_iam_role" "dr_backup" {
  assume_role_policy = data.aws_iam_policy_document.task_role_assume_policy.json
  name               = "dr-backup.${local.environment}"
  tags               = local.default_tags
}

data "aws_iam_policy_document" "dr_backup" {
  statement {
    sid       = "allowAssumeAccess"
    effect    = "Allow"
    resources = [aws_iam_role.cross_acc_backup.arn]
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
    resources = [
      "*"
    ]
  }
  statement {
    sid       = "allowSnapshotAccess"
    effect    = "Allow"
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
  name   = "dr-backup-task.${local.environment}"
  policy = data.aws_iam_policy_document.dr_backup.json
  role   = aws_iam_role.dr_backup.id
}

// SECURITY GROUPS

locals {
  dr_backup_sg_rules = {
    ecr     = local.common_sg_rules.ecr
    logs    = local.common_sg_rules.logs
    s3      = local.common_sg_rules.s3
    ssm     = local.common_sg_rules.ssm
    ecr_api = local.common_sg_rules.ecr_api
    dr_backup = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "0.0.0.0/0"
    }
  }
}

module "dr_backup_security_group" {
  source = "./security_group"
  rules  = local.dr_backup_sg_rules
  name   = "dr-backup"
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}
