module "restore_from_production" {
  source = "./task"
  name   = "restore-from-production"

  cluster_name          = aws_ecs_cluster.main.name
  container_definitions = "[${local.restore_from_production_container}]"
  tags                  = local.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution_role.arn
  subnet_ids            = data.aws_subnet.private[*].id
  task_role_arn         = data.aws_iam_role.sync.arn
  vpc_id                = data.aws_vpc.vpc.id
  security_group_id     = module.restore_from_production_security_group.id
}

locals {
  restore_from_production_sg_rules = {
    ecr  = local.common_sg_rules.ecr
    logs = local.common_sg_rules.logs
    s3   = local.common_sg_rules.s3
    rds = {
      port        = 5432
      protocol    = "tcp"
      type        = "egress"
      target_type = "security_group_id"
      target      = module.api_rds_security_group.id
    }
  }
}

module "restore_from_production_security_group" {
  source = "./security_group"
  rules  = local.restore_from_production_sg_rules
  name   = "restore-from-production"
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}

locals {
  restore_from_production_container = <<EOF
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
			"value": "production02"
		},
		{
			"name": "POSTGRES_DATABASE",
			"value": "${aws_rds_cluster.api.database_name}"
		},
		{
			"name": "POSTGRES_HOST",
			"value": "${aws_rds_cluster.api.endpoint}"
		},
		{
			"name": "POSTGRES_PORT",
			"value": "${aws_rds_cluster.api.port}"
		},
		{
			"name": "POSTGRES_USER",
			"value": "${aws_rds_cluster.api.master_username}"
		},
		{
			"name": "DROP_PUBLIC",
			"value": "yes"
		}
	]
}

EOF
}
