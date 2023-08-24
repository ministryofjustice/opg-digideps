module "restore" {
  source = "./modules/task"
  name   = "restore"

  cluster_name          = aws_ecs_cluster.main.name
  container_definitions = "[${local.restore_container}]"
  tags                  = local.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution_role.arn
  subnet_ids            = data.aws_subnet.private[*].id
  task_role_arn         = data.aws_iam_role.sync.arn
  vpc_id                = data.aws_vpc.vpc.id
  security_group_id     = module.restore_security_group.id
}

locals {
  restore_sg_rules = {
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

module "restore_security_group" {
  source      = "./modules/security_group"
  description = "Restore Database Service"
  rules       = local.restore_sg_rules
  name        = "restore"
  tags        = local.default_tags
  vpc_id      = data.aws_vpc.vpc.id
  environment = local.environment
}

locals {
  restore_container = <<EOF
{
	"name": "restore",
	"image": "${local.images.sync}",
    "command": ["./restore.sh"],
	"logConfiguration": {
		"logDriver": "awslogs",
		"options": {
			"awslogs-group": "${aws_cloudwatch_log_group.opg_digi_deps.name}",
			"awslogs-region": "eu-west-1",
			"awslogs-stream-prefix": "restore"
		}
	},
	"secrets": [{
		"name": "POSTGRES_PASSWORD",
		"valueFrom": "${data.aws_secretsmanager_secret.database_password.arn}"
	}],
	"environment": [{
			"name": "S3_BUCKET",
			"value": "${data.aws_s3_bucket.backup.bucket}"
		},
		{
			"name": "S3_PREFIX",
			"value": "${local.environment}"
		},
		{
			"name": "POSTGRES_DATABASE",
			"value": "${local.db.name}"
		},
		{
			"name": "POSTGRES_HOST",
			"value": "${local.db.endpoint}"
		},
		{
			"name": "POSTGRES_PORT",
			"value": "${local.db.port}"
		},
		{
			"name": "POSTGRES_USER",
			"value": "${local.db.username}"
		},
		{
			"name": "DROP_PUBLIC",
			"value": "yes"
		}
	]
}

EOF
}
